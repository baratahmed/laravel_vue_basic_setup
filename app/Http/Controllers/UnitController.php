<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UnitController extends Controller
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
        if(is_null($this->user) || !$this->user->can('unit.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $units = Unit::query()
                ->with('user:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%')->orWhere('short_name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return UnitResource::collection($units);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('unit.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:20',
            'short_name' => 'required|max:20',
        ]);
        $unit = new Unit();
        $unit->user_id = $request->user()->id;
        $unit->name = $request->name;
        $unit->short_name = $request->short_name;
        $unit->save();

        return send_msg("Unit Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('unit.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Unit::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('unit.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:20',
            'short_name' => 'required|max:20',
        ]);

        $unit = Unit::find($request->id);
        $unit->user_id = $request->user()->id;
        $unit->name = $request->name;
        $unit->short_name = $request->short_name;
        $unit->save();

        return send_msg("Unit Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        if(is_null($this->user) || !$this->user->can('unit.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $unit->delete();
        return send_msg('Delete Success', true, 200);
    }
}
