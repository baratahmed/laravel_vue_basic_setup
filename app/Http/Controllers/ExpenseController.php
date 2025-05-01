<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ExpenseController extends Controller
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
        if(is_null($this->user) || !$this->user->can('expense.read')){
            return send_msg('Unauthorized Access', false, 403);
        }

        $perPage = request('perPage') ?: 10;
        $expenses = Expense::query()
                ->with('user:id,name')
                ->with('expense_type:id,name')
                ->when((request('from') && request('to')), function($q){
                    $from = Carbon::parse(request('from'))->addHours(6)->toDateString();
                    $to = Carbon::parse(request('to'))->addHours(6)->toDateString();
                    return $q->whereBetween('date',[$from, $to]);
                })
                ->when((request('shop_id')), function($q){
                    return $q->where('shop_id',request('shop_id'));
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return ExpenseResource::collection($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('expense.create')){
            return send_msg('Unauthorized Access', false, 403);
        }

        // if($request->shop_id == null){
        //     return send_msg('Please, Select a shop',false, 200);
        // }
        if($request->expense_type == null){
            return send_msg('Please, Select a Expense Type',false, 200);
        }

        $request->validate([
            'expense_type' => 'required',
            // 'shop_id' => 'required',
            'user_id' => 'required',
            'reason' => 'required',
            'amount' => 'required',
            'date' => 'required',
        ]);
        $expense = new Expense();
        $expense->expense_type_id = $request->expense_type['id'];
        $expense->shop_id = $request->shop_id;
        $expense->user_id = $request->user_id;
        $expense->date = Carbon::parse($request->date)->addHours(6)->toDateString();
        $expense->reason = $request->reason;
        $expense->amount = $request->amount;
        $expense->save();

        return send_msg("Expense Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Expense::with('user:id,name')->with('expense_type:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('expense.update')){
            return send_msg('Unauthorized Access', false, 403);
        }

        if($request->shop_id == null){
            return send_msg('Please, Select a shop',false, 200);
        }
        if($request->expense_type == null){
            return send_msg('Please, Select a Expense Type',false, 200);
        }

        $request->validate([
            'id' => 'required',
            'expense_type' => 'required',
            'shop_id' => 'required',
            'user_id' => 'required',
            'reason' => 'required',
            'amount' => 'required',
            'date' => 'required',
        ]);

        $expense = Expense::find($request->id);
        $expense->expense_type_id = $request->expense_type['id'];
        $expense->shop_id = $request->shop_id;
        $expense->user_id = $request->user_id;
        $expense->date = strlen($request->date) <= 12 ? $request->date : Carbon::parse($request->date)->addHours(6)->toDateString();
        $expense->reason = $request->reason;
        $expense->amount = $request->amount;
        $expense->save();

        return send_msg("Expense Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(is_null($this->user) || !$this->user->can('expense.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }

        $exp = Expense::find($id);
        $exp->delete();
        return send_msg('Delete Success', true, 200);
    }
}
