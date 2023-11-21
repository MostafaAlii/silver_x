<?php
namespace App\Http\Controllers\Dashboard\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\Dashboard\Admin\SubscriptionDataTable;
class SubscriptionController extends Controller {
    public function __construct(protected SubscriptionDataTable $dataTable,) {
        $this->dataTable = $dataTable;
    }
    public function index() {
        $data = [
            'title' => 'Subscriptions',
        ];
        return $this->dataTable->render('dashboard.admin.subscriptions.index',  compact('data'));
    }

    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
