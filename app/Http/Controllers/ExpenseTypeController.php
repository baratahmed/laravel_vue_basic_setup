<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseTypeResource;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ExpenseTypeController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function($request,$next){
            $this->user = Auth::guard('sanctum')->user();
            return $next($request);
        });
    }

    public function index()
    {
        if(is_null($this->user) || !$this->user->can('expensetype.read')){
            return send_msg('Unauthorized Access', false, 403);
        }

        $perPage = request('perPage') ?: 10;
        $expense_types = ExpenseType::query()
                ->with('user:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return ExpenseTypeResource::collection($expense_types);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('expensetype.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:20',
            'user_id' => 'required',
        ]);
        $expense_type = new ExpenseType();
        $expense_type->user_id = $request->user_id;
        $expense_type->name = $request->name;
        $expense_type->save();

        return send_msg("Expense Type Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('expensetype.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return ExpenseType::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('expensetype.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'id' => 'required',
            'name' => 'required|max:20',
            'user_id' => 'required',
        ]);

        $expense_type = ExpenseType::find($request->id);
        $expense_type->user_id = $request->user()->id;
        $expense_type->name = $request->name;
        $expense_type->save();

        return send_msg("Expense Type Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(is_null($this->user) || !$this->user->can('expensetype.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $et = ExpenseType::find($id);
        $et->delete();
        return send_msg('Delete Success', true, 200);
    }
}
