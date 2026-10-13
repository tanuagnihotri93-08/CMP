<?php

namespace Tests\Feature;

use App\Exports\ReportExport;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_filtered_reports_and_export_filtered_excel_with_audit_log(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager);
        $today = now();

        Lead::create([
            'name' => 'Current lead',
            'status' => 'new',
            'assigned_to_user_id' => $manager->id,
        ]);
        $olderLead = Lead::create([
            'name' => 'Older lead',
            'status' => 'new',
            'assigned_to_user_id' => $manager->id,
        ]);
        $olderLead->forceFill(['created_at' => $today->copy()->subDays(5)])->save();

        $this->get(route('reports.index', [
            'date_from' => $today->toDateString(),
            'date_to' => $today->toDateString(),
            'status' => 'new',
            'module' => 'leads',
        ]))->assertOk()->assertSee('Leads Report');

        $response = $this->get(route('reports.export', [
            'module' => 'leads',
            'format' => 'excel',
            'date_from' => $today->toDateString(),
            'date_to' => $today->toDateString(),
            'status' => 'new',
        ]));

        $response->assertOk()
            ->assertHeader('content-disposition');
        $this->assertTrue($manager->hasPermission('reports.export'));
        $this->assertSame(1, AuditLog::where('module', 'reports')->latest()->firstOrFail()->new_values['row_count']);
    }

    public function test_pdf_exports_are_audited(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager);

        $this->get(route('reports.export', ['module' => 'activities', 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $manager->id,
            'module' => 'reports',
            'action' => 'exported',
            'description' => "{$manager->name} exported Activities Report PDF.",
        ]);
    }

    public function test_lost_leads_are_available_in_pipeline_reports(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager);
        Lead::create([
            'name' => 'Lost prospect',
            'status' => 'lost',
            'assigned_to_user_id' => $manager->id,
        ]);

        $this->get(route('reports.index', ['module' => 'leads', 'status' => 'lost']))
            ->assertOk()
            ->assertSee('Won / Converted');
        $this->get(route('reports.export', [
            'module' => 'leads',
            'format' => 'excel',
            'status' => 'lost',
        ]))->assertOk();

        $this->assertSame(1, AuditLog::where('module', 'reports')->latest()->firstOrFail()->new_values['row_count']);
    }

    public function test_status_filter_must_belong_to_the_selected_report_module(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager);

        $this->get(route('reports.index', ['module' => 'customers', 'status' => 'won']))
            ->assertSessionHasErrors('status');
    }

    public function test_employee_cannot_access_reports_or_export_data(): void
    {
        $employee = $this->userWithRole('employee');
        $this->actingAs($employee);

        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('reports.export', ['module' => 'leads', 'format' => 'excel']))->assertForbidden();
        $this->assertDatabaseMissing('audit_logs', ['module' => 'reports']);
    }

    public function test_excel_export_escapes_formula_like_user_content(): void
    {
        $this->assertSame(
            ['\'=HYPERLINK("https://example.test")'],
            ReportExport::escapeFormulaValues(['=HYPERLINK("https://example.test")'])
        );
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->forceFill([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ])->save();

        return $user;
    }
}
