<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Client;
use App\Models\Exercise;
use App\Models\Position;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\EmployeePayroll;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\StaffShowResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ClientShowResource;
use App\Http\Responses\ValidationResponse;
use App\Http\Resources\ExerciseShowResource;
use App\Http\Resources\PositionShowResource;
use App\Http\Resources\InventoryShowResource;
use App\Http\Resources\EmployeePayrollResource;
use App\Http\Resources\EmployeeAttendanceResource;

class AdminController extends Controller
{
    public function show_clients(){
        $clients = Client::all();
        return ClientShowResource::collection($clients);
    }

    public function store_clients(Request $request){
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'gender' => 'required',
            'contact_no' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $client = Client::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'address' => $request->address,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client created successfully'
        ], 201);
    }

    public function edit_clients(Request $request, $id){
        $client = Client::find($id);
        if(!$client){
            return response()->json([
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client retrieved successfully'
        ], 200);
    }

    public function update_clients(Request $request, $id){
        $client = Client::find($id);

        if(!$client){
            return response()->json([
                'message' => 'Client not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'gender' => 'required',
            'contact_no' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $client->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'address' => $request->address,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client updated successfully'
        ], 200);
    }

    public function soft_delete_clients(Request $request, $id){
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully'], 200);
    }

    public function trashed_record_clients(){
        $trashed = Client::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No clients found'
            ], 404);
        }

        return response()->json([
            'data' => ClientShowResource::collection($trashed),
            'message' => 'Clients retrieved successfully'
        ]);
    }

    public function force_delete_clients(Request $request, $id){
        $delete = Client::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Client was permanently deleted'
        ]);
    }

    public function restore_clients(Request $request, $id){
        $restore = Client::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        $restore->restore();

        return response()->json([
            'message' => 'Client restored successfully',
            'data' => new ClientShowResource($restore)
        ]);
    }

    public function show_staffs(){
        $staffs = Staff::with('position')->get();
        return StaffShowResource::collection($staffs);
    }

    public function store_staffs(Request $request){
        $validator = Validator::make($request->all(), [
            'position_id' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'address' => 'required',
            'gender' => 'required',
            'contact_no' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $today = Carbon::now()->format('Y/m/d');

        $staff = Staff::create([
            'position_id' => $request->position_id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'gender' => $request->gender,
            'joined_date' => $today,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new StaffShowResource($staff),
            'message' => 'Staff created successfully'
        ], 201);
    }

    public function edit_staffs(Request $request, $id){
        $staff = Staff::find($id);
        if(!$staff){
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'data' => new StaffShowResource($staff),
            'message' => 'Staff retrieved successfully'
        ], 200);
    }

    public function update_staffs(Request $request, $id){
        $staff = Staff::find($id);

        if(!$staff){
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'position_id' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'gender' => 'required',
            'contact_no' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $staff->update([
            'position_id' => $request->position_id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new StaffShowResource($staff),
            'message' => 'Staff updated successfully'
        ], 200);
    }

    public function soft_delete_staffs(Request $request, $id){
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $staff->delete();

        return response()->json(['message' => 'Staff deleted successfully'], 200);
    }

    public function trashed_record_staffs(){
        $trashed = Staff::onlyTrashed()->get();

        return response()->json([
            'data' => StaffShowResource::collection($trashed),
            'message' => 'Trashed records retrieved successfully'
        ]);
    }

    public function force_delete_staffs(Request $request, $id){
        $delete = Staff::onlyTrashed()->find($id);
        $delete->forceDelete();

        return response()->json([
            'message' => 'Staff was permanently deleted'
        ]);
    }

    public function restore_staffs(Request $request, $id){
        $restore = Staff::onlyTrashed()->find($id);
        $restore->restore();

        return response()->json([
            'message' => 'Staff restored successfully',
            'data' => new StaffShowResource($restore)
        ]);
    }

    public function show_exercises(){
        $exercises = Exercise::all();
        return ExerciseShowResource::collection($exercises);
    }

    public function store_exercises(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'short_description' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $exercise = Exercise::create([
            'name' => $request->name,
            'price' => $request->price,
            'tag' => $request->tag,
            'short_description' => $request->short_description
        ]);

        return response()->json([
            'data' => new ExerciseShowResource($exercise),
            'message' => 'Exercise created successfully'
        ], 201);
    }

    public function edit_exercises(Request $request, $id){
        $exercise = Exercise::find($id);
        if(!$exercise){
            return response()->json([
                'message' => 'Exercise not found'
            ], 404);
        }

        return response()->json([
            'data' => new ExerciseShowResource($exercise),
            'message' => 'Exercise retrieved successfully'
        ], 200);
    }

    public function update_exercises(Request $request, $id){
        $exercise = Exercise::find($id);

        if(!$exercise){
            return response()->json([
                'message' => 'Exercise not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'short_description' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $exercise->update([
            'name' => $request->name,
            'price' => $request->price,
            'tag' => $request->tag,
            'short_description' => $request->short_description
        ]);

        return response()->json([
            'data' => new ExerciseShowResource($exercise),
            'message' => 'Exercise updated successfully'
        ], 200);
    }

    public function soft_delete_exercises(Request $request, $id){
        $soft = Exercise::find($id);

        if (!$soft) {
            return response()->json(['message' => 'Exercise not found'], 404);
        }

        $soft->delete();

        return response()->json(['message' => 'Exercise deleted successfully'], 200);
    }

    public function trashed_record_exercise(){
        $trashed = Exercise::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Exercise found'
            ], 404);
        }

        return response()->json([
            'data' => ExerciseShowResource::collection($trashed),
            'message' => 'Exercises retrieved successfully'
        ]);
    }


    public function hard_delete_exercises(Request $request, $id){
        $delete = Exercise::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Exercise not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Exercise was permanently deleted'
        ]);
    }

    public function restore_exercises(Request $request, $id){
        $restore = Exercise::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Exercise not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Exercise restored successfully',
            'data' => new ExerciseShowResource($restore)
        ]);
    }

    public function show_positions(){
        $positions = Position::all();
        return PositionShowResource::collection($positions);
    }

    public function store_positions(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $position = Position::create([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => new PositionShowResource($position),
            'message' => 'Position created successfully'
        ], 201);
    }

    public function edit_positions(Request $request, $id){
        $position = Position::find($id);
        if(!$position){
            return response()->json([
                'message' => 'Position not found'
            ], 404);
        }

        return response()->json([
            'data' => new PositionShowResource($position),
            'message' => 'Position retrieved successfully'
        ], 200);
    }

    public function update_positions(Request $request, $id){
        $position = Position::find($id);

        if(!$position){
            return response()->json([
                'message' => 'Position not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $position->update([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => new PositionShowResource($position),
            'message' => 'Position updated successfully'
        ], 200);
    }

    public function soft_delete_positions(Request $request, $id){
        $soft = Position::find($id);

        if (!$soft) {
            return response()->json(['message' => 'Position not found'], 404);
        }

        $soft->delete();

        return response()->json(['message' => 'Position deleted successfully'], 200);
    }

    public function trashed_record_positions(){
        $trashed = Position::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Position found'
            ], 404);
        }

        return response()->json([
            'data' => PositionShowResource::collection($trashed),
            'message' => 'Postions retrieved successfully'
        ]);
    }

    public function hard_delete_positions(Request $request, $id){
        $delete = Position::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Position not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Position was permanently deleted'
        ]);
    }


    public function restore_positions(Request $request, $id){
        $restore = Position::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Position not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Position restored successfully',
        ]);
    }

    public function show_inventories(){
        $inventories = Inventory::all();
        return InventoryShowResource::collection($inventories);
    }

    public function store_inventories(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'short_description' => 'required',
            'quantity' => 'required',
            'price' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $inventory = Inventory::create([
            'item_code' => uniqid(),
            'name' => $request->name,
            'type' => $request->type,
            'short_description' => $request->short_description,
            'quantity' => $request->quantity,
            'price' => $request->price
        ]);

        return response()->json([
            'data' => new InventoryShowResource($inventory),
            'message' => 'Inventory created successfully'
        ], 201);
    }

    public function edit_inventories(Request $request, $id){
        $inventory = Inventory::find($id);
        if(!$inventory){
            return response()->json([
                'message' => 'Inventory not found'
            ], 404);
        }

        return response()->json([
            'data' => new InventoryShowResource($inventory),
            'message' => 'Inventory retrieved successfully'
        ], 200);
    }

    public function update_inventories(Request $request, $id){
        $inventory = Inventory::find($id);

        if(!$inventory){
            return response()->json([
                'message' => 'Inventory not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'short_description' => 'required',
            'quantity' => 'required',
            'price' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $inventory->update([
            'name' => $request->name,
            'type' => $request->type,
            'short_description' => $request->short_description,
            'quantity' => $request->quantity,
            'price' => $request->price
        ]);

        return response()->json([
            'data' => new InventoryShowResource($inventory),
            'message' => 'Inventory updated successfully'
        ], 200);
    }

    public function soft_delete_inventories(Request $request, $id){
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        $inventory->delete();

        return response()->json(['message' => 'Inventory deleted successfully'], 200);
    }

    public function trashed_record_inventories(){
        $trashed = Inventory::onlyTrashed()->get();

        if (!$trashed) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        return response()->json([
            'data' => InventoryShowResource::collection($trashed),
            'message' => 'Inventory retrieved successfully'
        ]);
    }

    public function hard_delete_inventories(Request $request, $id){
        $delete = Inventory::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }
        $delete->forceDelete();

        return response()->json([
            'message' => 'Inventory permanently deleted successfully'
        ], 200);
    }

    public function restore_inventories(Request $request, $id){
        $restore = Inventory::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Inventory restored successfully'
        ]);
    }

    public function show_staff_attendances(){
        $attendances = EmployeeAttendance::with('staff')->get();

        return EmployeeAttendanceResource::collection($attendances);
    }

    public function store_staff_attendances(Request $request, $id){
        $staff = Staff::find($id);
        if(!$staff){
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }

        $today = Carbon::now()->format('Y-m-d');

        if($request->date != $today){
            return response()->json([
                'message' => 'Date is not today'
            ], 400);
        }

        $attendance = EmployeeAttendance::where('staff_id', $staff->id)
                        ->where('date', $today)->first();

        if($attendance){
            return response()->json([
                'message' => 'Attendance already filled'
            ], 400);
        }


        $validator = Validator::make($request->all(), [
            'staff_id' => 'required',
            'date' => 'required',
            'attendance' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }


        $attendance = EmployeeAttendance::create([
            'staff_id' => $request->staff_id,
            'date' => $request->date,
            'attendance' => $request->attendance,
        ]);

        return response()->json([
            'data' => new EmployeeAttendanceResource($attendance),
            'message' => 'Attendance created successfully'
        ], 201);
    }

    public function edit_staff_attendances(Request $request, $id){
        $attendance = EmployeeAttendance::find($id);
        if(!$attendance){
            return response()->json([
                'message' => 'Attendance not found'
            ], 404);
        }

        return response()->json([
            'data' => new EmployeeAttendanceResource($attendance),
            'message' => 'Attendance retrieved successfully'
        ], 200);
    }

    public function update_staff_attendances(Request $request, $id){
        $attendance = EmployeeAttendance::find($id);

        if(!$attendance){
            return response()->json([
                'message' => 'Attendance not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'attendance' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $attendance->update([
            'attendance' => $request->attendance,
        ]);

        return response()->json([
            'data' => new EmployeeAttendanceResource($attendance),
            'message' => 'Attendance updated successfully'
        ], 200);
    }

    public function soft_delete_staff_attendances(Request $request, $id){
        $soft = EmployeeAttendance::find($id);

        if(!$soft){
            return response()->json([
                'message' => 'Attendance not found'
            ], 404);
        }

        $soft->delete();

        return response()->json([
            'message' => 'Attendance deleted successfully'
        ]);
    }

    public function trashed_record_attendancess(){
        $trashed = EmployeeAttendance::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Attendance found'
            ], 404);
        }

        return response()->json([
            'data' => EmployeeAttendanceResource::collection($trashed),
            'message' => 'Attendances retrieved successfully'
        ]);
    }

    public function hard_delete_staff_attendances(Request $request, $id){
        $delete = EmployeeAttendance::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Attendance permanently deleted successfully'
        ]);
    }

    public function restore_staff_attendances(Request $request, $id){
        $restore = EmployeeAttendance::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Attendance restored successfully'
        ]);
    }

    public function show_staff_payrolls(){
        $payroll = EmployeePayroll::with('staff')->get();

        return response()->json([
            'data' => EmployeePayrollResource::collection($payroll)->map(function($item) {
                $absent = $item->staff->attendances()->whereBetween('date', [$item->start_date, $item->end_date])->where('attendance', 'absent')->count();
                $whole_day = $item->staff->attendances()->whereBetween('date', [$item->start_date, $item->end_date])->where('attendance', 'present')->count();
                $half_day = $item->staff->attendances()->whereBetween('date', [$item->start_date, $item->end_date])->where('attendance', 'halfday')->count();

                return [
                    'id' => $item->id,
                    'name' => $item->staff->firstname . ' ' . $item->staff->lastname,
                    'present_day' => $item->present_day,
                    'total_salary' => $item->total_salary,
                    'overtime' => $item->over_time,
                    'yearly_bonus' => $item->yearly_bonus,
                    'sales_comission' => $item->sales_comission,
                    'incentives' => $item->incentives,
                    'sss' => $item->sss,
                    'pag_ibig' => $item->pag_ibig,
                    'philhealth' => $item->philhealth,
                    'net_income' => $item->net_income,
                    'total_deductions' => $item->total_deductions,
                    'final_salary' => $item->final_salary,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'pay_date' => $item->pay_date,
                    'absent' => $absent,
                    'whole_day' => $whole_day,
                    'half_day' => $half_day
                ];
            }),
            'message' => 'Payroll retrieved successfully',
        ], 200);
    }

    public function store_staff_payrolls(Request $request, $id){
        $staff = Staff::find($id);
        if(!$staff){
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'pay_date' => 'required'
        ]);

        $id = $staff->id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $pay_date = $request->pay_date;

        $query = EmployeeAttendance::query()
        ->when($id, function ($query, $id) {
            return $query->where('id', $id);
        })
        ->when($start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })
        ->when($end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        // $filteredData = $query->with('staff.position')->get();
        // $present_days = $query->count();

        $whole_days = $query->clone()->where('attendance', 'present')->count();
        $half_days = $query->clone()->where('attendance', 'halfday')->count();
        $present_day = $whole_days + $half_days;

        $rate_per_day = 0;
        switch ($staff->position_id) {
            // Instructor Rate
            case 2:
                $rate_per_day = 520;
                break;
            // Cashier Rate
            case 3:
                $rate_per_day = 520;
                break;
            // Manager Rate
            case 4:
                $rate_per_day = 1000;
                break;
            // Utility Rate
            case 5:
                $rate_per_day = 300;
                break;
        }

        $whole_day_salary = $rate_per_day * $whole_days;

        $half_day_rate = $rate_per_day / 2;
        $half_day_salary = $half_day_rate * $half_days;

        $total_salary = $whole_day_salary + $half_day_salary;

        // Additional Incomes
        $overtime = $request->over_time;
        $yearly_bonus = $request->yearly_bonus;
        $sales_comission = $request->sales_comission;
        $incentives = $request->incentives;

        $net_income = $total_salary + $overtime + $yearly_bonus + $sales_comission + $incentives;

        // Deductions
        $sss = 0;
        $pag_ibig = 0;
        $philhealth = 0;
        if (date('d', strtotime($start_date)) <= 15 && date('d', strtotime($end_date)) >= 15) {
            $sss = 0.01 * $net_income;
        }

        if (date('d', strtotime($start_date)) <= 30 && date('d', strtotime($end_date)) >= 30) {
            $pag_ibig = 0.04 * $net_income;
            $philhealth = 0.01 * $net_income;
        }

        $total_deductions = $staff->position_id === 5 ? 0 : ($sss + $pag_ibig + $philhealth);

        $final_salary = $net_income - $total_deductions;

        $payroll = EmployeePayroll::create([
            'staff_id' => $staff->id,
            'present_day' => $present_day,
            'total_salary' => $total_salary,
            'whole_day_salary' => $whole_day_salary,
            'half_day_salary' => $half_day_salary,
            'over_time' => $overtime,
            'yearly_bonus' => $yearly_bonus,
            'sales_comission' => $sales_comission,
            'incentives' => $incentives,
            'sss' => $sss,
            'pag_ibig' => $pag_ibig,
            'philhealth' => $philhealth,
            'net_income' => $net_income,
            'total_deductions' => $total_deductions,
            'final_salary' => $final_salary,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pay_date' => $pay_date
        ]);

        return response()->json([
            'data' => new EmployeePayrollResource($payroll),
            'message' => 'Payroll retrieved successfully'
        ]);
    }

    public function soft_delete_staff_payrolls(Request $request, $id){
        $soft = EmployeePayroll::find($id);

        if(!$soft){
            return response()->json([
                'message' => 'Payroll not found'
            ], 404);
        }

        $soft->delete();

        return response()->json([
            'message' => 'Payroll deleted successfully'
        ]);
    }

    public function trashed_record_payrolls(){
        $trashed = EmployeePayroll::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Payrolls found'
            ], 404);
        }

        return response()->json([
            'data' => EmployeePayrollResource::collection($trashed),
            'message' => 'Payrolls retrieved successfully'
        ]);
    }

    public function hard_delete_staff_payrolls(Request $request, $id){
        $delete = EmployeePayroll::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Payrolls not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Payrolls permanently deleted successfully'
        ]);
    }

    public function restore_staff_payrolls(Request $request, $id){
        $restore = EmployeePayroll::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Payroll restored successfully'
        ]);
    }

    public function backup()
    {
        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $database = env('DB_DATABASE');
            $key = "Tables_in_$database";

            $backupData = '';

            foreach ($tables as $table) {
                $tableName = $table->$key;

                // Fetch table data
                $data = DB::table($tableName)->get();

                // Create a basic SQL insert for each table
                $backupData .= "DROP TABLE IF EXISTS `$tableName`;\n";
                $columns = Schema::getColumnListing($tableName); // Get column names

                // Generate table creation SQL
                $createTableSQL = "CREATE TABLE `$tableName` (\n";
                foreach ($columns as $column) {
                    $columnType = DB::getSchemaBuilder()->getColumnType($tableName, $column);
                    $createTableSQL .= "`$column` $columnType,\n";
                }
                $createTableSQL = rtrim($createTableSQL, ",\n") . "\n);";
                $backupData .= $createTableSQL . "\n\n";

                // Generate insert SQL for each row
                foreach ($data as $row) {
                    $insertSQL = "INSERT INTO `$tableName` (" . implode(', ', $columns) . ") VALUES (";

                    $values = [];
                    foreach ($columns as $column) {
                        $values[] = "'" . addslashes($row->$column) . "'"; // Escape values
                    }

                    $insertSQL .= implode(', ', $values) . ");\n";
                    $backupData .= $insertSQL;
                }

                $backupData .= "\n\n";
            }

            // Define the backup file name
            $filename = 'database_backup_' . date('Y_m_d_H_i_s') . '.sql';

            // Store in local storage (storage/app)
            Storage::put($filename, $backupData);

            return response()->json([
                'status' => 'success',
                'message' => 'Database backup created successfully!',
                'file' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to backup database.',
                'error' => $e->getMessage(),
            ]);
        }
    }

}
