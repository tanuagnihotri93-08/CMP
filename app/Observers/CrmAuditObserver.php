<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\CrmRecordNotification;
use Illuminate\Database\Eloquent\Model;

class CrmAuditObserver
{
    public function created(Model $model): void
    {
        [$module, $recordType, $label] = $this->identity($model);
        $values = $this->values($model, $model->getAttributes());

        AuditLog::record(
            'created',
            $module,
            $recordType,
            (int) $model->getKey(),
            "{$label} created.",
            null,
            $values
        );

        $this->notifyAssignee($model, "{$label} assigned to you.", 'A new CRM record is ready for you.');

        if ($model instanceof Customer && $model->lead_id !== null) {
            $lead = Lead::find($model->lead_id);

            AuditLog::record(
                'converted',
                'leads',
                Lead::class,
                (int) $model->lead_id,
                'Lead converted to a customer.',
                $lead ? ['status' => $lead->status] : null,
                ['customer_id' => (int) $model->getKey()]
            );
        }
    }

    public function updated(Model $model): void
    {
        [$module, $recordType, $label] = $this->identity($model);
        $changedKeys = array_values(array_intersect(
            array_keys($model->getChanges()),
            $model->getFillable()
        ));

        if ($changedKeys === []) {
            return;
        }

        $old = [];
        $new = [];

        foreach ($changedKeys as $key) {
            $old[$key] = $model->getOriginal($key);
            $new[$key] = $model->getAttribute($key);
        }

        AuditLog::record(
            'updated',
            $module,
            $recordType,
            (int) $model->getKey(),
            "{$label} updated.",
            $old,
            $new
        );

        if (array_key_exists('assigned_to_user_id', $new) && $new['assigned_to_user_id'] !== null) {
            $this->notifyAssignee($model, "{$label} assigned to you.", 'A CRM record was assigned to you.');
        }

        if (array_key_exists('status', $new) && $old['status'] !== $new['status']) {
            $action = 'status_changed';

            if (
                ($model instanceof FollowUp || $model instanceof Activity)
                && $new['status'] === 'completed'
            ) {
                $action = 'completed';
            }

            AuditLog::record(
                $action,
                $module,
                $recordType,
                (int) $model->getKey(),
                "{$label} status changed from ".($old['status'] ?? '—')." to {$new['status']}.",
                ['status' => $old['status'] ?? null],
                ['status' => $new['status']]
            );
        }
    }

    public function deleting(Model $model): void
    {
        if (! $model instanceof Lead) {
            return;
        }

        $customer = $model->customer()->first();

        if ($customer) {
            AuditLog::record(
                'deleted',
                'customers',
                Customer::class,
                (int) $customer->getKey(),
                'Customer deleted when its linked lead was deleted.',
                $this->values($customer, $customer->getAttributes())
            );
        }
    }

    public function deleted(Model $model): void
    {
        [$module, $recordType, $label] = $this->identity($model);

        AuditLog::record(
            'deleted',
            $module,
            $recordType,
            (int) $model->getKey(),
            "{$label} deleted.",
            $this->values($model, $model->getAttributes())
        );
    }

    private function identity(Model $model): array
    {
        return match (true) {
            $model instanceof Lead => ['leads', Lead::class, 'Lead'],
            $model instanceof Customer => ['customers', Customer::class, 'Customer'],
            $model instanceof FollowUp => ['follow_ups', FollowUp::class, 'Follow up'],
            $model instanceof Activity => ['activities', Activity::class, 'Activity'],
            default => throw new \InvalidArgumentException('Unsupported CRM audit model.'),
        };
    }

    private function values(Model $model, array $attributes): array
    {
        return array_intersect_key($attributes, array_flip($model->getFillable()));
    }

    private function notifyAssignee(Model $model, string $title, string $message): void
    {
        $assigneeId = $model->getAttribute('assigned_to_user_id');
        if (! $assigneeId) {
            return;
        }

        $assignee = User::query()->whereKey($assigneeId)->where('is_active', true)->first();
        if (! $assignee || $assignee->is(auth()->user())) {
            return;
        }

        [$module] = $this->identity($model);
        $assignee->notify(new CrmRecordNotification(
            $title,
            $message,
            $module,
            (int) $model->getKey()
        ));
    }
}
