<?php
namespace App\DataTables\Dashboard\Admin;
use App\Models\Subscription;
use App\DataTables\Base\BaseDataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Utilities\Request as DataTableRequest;
class SubscriptionDataTable extends BaseDataTable {
    public function __construct(DataTableRequest $request) {
        parent::__construct(new Subscription());
        $this->request = $request;
    }

    public function dataTable($query): EloquentDataTable {
        return (new EloquentDataTable($query))
            ->addColumn('action', function (Subscription $subscription) {
                return view('dashboard.admin.subscriptions.btn.actions', compact('subscription'));
            })
            ->editColumn('created_at', function (Subscription $subscription) {
                return $this->formatBadge($this->formatDate($subscription->created_at));
            })
            ->editColumn('updated_at', function (Subscription $subscription) {
                return $this->formatBadge($this->formatDate($subscription->updated_at));
            })
            ->editColumn('status', function (Subscription $subscription) {
                return $this->formatStatus($subscription->status);
            })
            ->editColumn('admin_id', function (Subscription $subscription) {
                return $subscription->admin->name;
            })
            ->editColumn('employee_id', function (Subscription $subscription) {
                return $subscription->employee->name;
            })
            ->editColumn('agent_id', function (Subscription $subscription) {
                return $subscription->agent->name;
            })
            ->editColumn('company_id', function (Subscription $subscription) {
                return $subscription->company->name;
            })
            ->rawColumns(['action', 'created_at', 'updated_at','status','admin_id', 'employee_id', 'agent_id', 'company_id']); 
    }

    public function query(): QueryBuilder {
        return Subscription::with(['admin','employee','agent','company'])->latest();
    }

    public function getColumns(): array {
        return [
            ['name' => 'id', 'data' => 'id', 'title' => '#', 'orderable' => false, 'searchable' => false,],
            ['name' => 'name', 'data' => 'name', 'title' => 'Name',],
            ['name' => 'email', 'data' => 'email', 'title' => 'Email',],
            ['name' => 'phone', 'data' => 'phone', 'title' => 'Phone',],
            ['name' => 'admin_id', 'data' => 'admin_id', 'title' => 'Admin',],
            ['name' => 'agent_id', 'data' => 'agent_id', 'title' => 'Agent',],
            ['name' => 'employee_id', 'data' => 'employee_id', 'title' => 'Employee',],
            ['name' => 'company_id', 'data' => 'company_id', 'title' => 'Company',],
            ['name' => 'value', 'data' => 'value', 'title' => 'Value',],
            ['name' => 'price', 'data' => 'price', 'title' => 'Price',],
            ['name' => 'status', 'data' => 'status', 'title' => 'Status',],
            ['name' => 'start_data', 'data' => 'start_data', 'title' => 'Start Date', 'orderable' => false, 'searchable' => false,],
            ['name' => 'end_data', 'data' => 'end_data', 'title' => 'End Date', 'orderable' => false, 'searchable' => false,],
            ['name' => 'created_at', 'data' => 'created_at', 'title' => 'Created_at', 'orderable' => false, 'searchable' => false,],
            ['name' => 'updated_at', 'data' => 'updated_at', 'title' => 'Update_at', 'orderable' => false, 'searchable' => false,],
            ['name' => 'action', 'data' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false,],
        ];
    }
}