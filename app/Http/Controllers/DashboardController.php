<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function($request,$next){
            $this->user = Auth::guard('sanctum')->user();
            return $next($request);
        });
    }

    public function index(Request $request){
        if(is_null($this->user) || !$this->user->can('dashboard.read')){
            return send_msg('Unauthorized Access', false, 403);
        }

        $from = Carbon::parse(request('from'))->addHours(6)->toDateString();
        $to = Carbon::parse(request('to'))->addHours(6)->toDateString();





        return [
            'expense_count' => 1,
            'purchase_gt_count' => 1,
            'purchase_paid_count' => 1,
            'purchase_due_count' => 1,
            'sale_gt_count' => 1,
            'sale_paid_count' => 1,
            'sale_due_count' => 1,
            'profit_loss' => 1,
        ];
    }
}
