<?php

namespace App\Http\Controllers;

use App\Http\Resources\MallResource;
use App\Models\Mall;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;


class MallController extends Controller
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
        if(is_null($this->user) || !$this->user->can('mall.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $malls = Mall::query()
                ->with('user:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
                
        return MallResource::collection($malls);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('mall.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:50',
            'location' => 'required',         
        ]);
        $mall = new Mall();
        $mall->user_id = $request->user()->id;
        $mall->name = $request->name;
        $mall->location = $request->location;
        $mall->save();
        return send_msg("Mall Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('mall.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Mall::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('mall.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'id' => 'required',
            'name' => 'required|max:50',
            'location' => 'required',
        ]);

        $mall = Mall::find($request->id);
        $mall->user_id = $request->user()->id;
        $mall->name = $request->name;
        $mall->location = $request->location;
        $mall->save();

        return send_msg("Mall Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mall $mall)
    {
        if(is_null($this->user) || !$this->user->can('mall.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $mall->delete();
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('mall.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $malls = Mall::whereIn('id',request('ids'))->get();
        foreach ($malls as $mall) {
            $mall->delete();
        }
        return send_msg('Delete Success', true, 200);

    }
}
