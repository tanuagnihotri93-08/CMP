<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityTimeline
{
    public function forLead(Lead $lead, User $viewer): LengthAwarePaginator
    {
        $records = [Lead::class => [$lead->getKey()]];

        if ($viewer->hasPermission('activities.view')) {
            $records[Activity::class] = $viewer
                ->constrainToAssignedRecords($lead->activities()->getQuery())
                ->select('activities.id');
        }

        if ($viewer->hasPermission('follow_ups.view')) {
            $records[FollowUp::class] = $viewer
                ->constrainToAssignedRecords($lead->followUps()->getQuery())
                ->select('follow_ups.id');
        }

        $customer = $lead->relationLoaded('customer') ? $lead->getRelation('customer') : null;
        if (
            $customer
            && $viewer->hasPermission('customers.view')
            && $viewer->canAccessRecord($customer)
        ) {
            $records[Customer::class] = [$customer->getKey()];
        }

        return $this->paginateEvents($records);
    }

    public function forCustomer(Customer $customer, User $viewer): LengthAwarePaginator
    {
        $records = [Customer::class => [$customer->getKey()]];

        if ($viewer->hasPermission('activities.view')) {
            $records[Activity::class] = $viewer
                ->constrainToAssignedRecords($customer->activities()->getQuery())
                ->select('activities.id');
        }

        $lead = $customer->relationLoaded('lead') ? $customer->getRelation('lead') : null;
        if (
            $lead
            && $viewer->hasPermission('leads.view')
            && $viewer->canAccessRecord($lead)
        ) {
            $records[Lead::class] = [$lead->getKey()];

            if ($viewer->hasPermission('follow_ups.view')) {
                $records[FollowUp::class] = $viewer
                    ->constrainToAssignedRecords($lead->followUps()->getQuery())
                    ->select('follow_ups.id');
            }
        }

        return $this->paginateEvents($records);
    }

    /**
     * @param  array<class-string, array<int, int|string>|Builder>  $records
     */
    private function paginateEvents(array $records): LengthAwarePaginator
    {
        $records = array_filter(
            $records,
            fn (array|Builder $ids) => ! is_array($ids) || $ids !== []
        );

        return AuditLog::query()
            ->where(function (Builder $query) use ($records): void {
                foreach ($records as $recordType => $recordIds) {
                    $query->orWhere(function (Builder $recordQuery) use ($recordType, $recordIds): void {
                        $recordQuery
                            ->where('record_type', $recordType)
                            ->whereIn('record_id', $recordIds);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'timeline_page')
            ->withQueryString();
    }
}
