<?php

namespace App\Http\Controllers;

use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SizeController extends Controller
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
        if(is_null($this->user) || !$this->user->can('size.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $sizes = Size::query()
                ->with('user:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%')->orWhere('short_name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return SizeResource::collection($sizes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('size.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:20',
            'short_name' => 'required|max:20',
        ]);
        $size = new Size();
        $size->user_id = $request->user()->id;
        $size->name = $request->name;
        $size->short_name = $request->short_name;
        $size->save();

        return send_msg("Size Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('size.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Size::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('size.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:20',
            'short_name' => 'required|max:20',
        ]);

        $size = Size::find($request->id);
        $size->user_id = $request->user()->id;
        $size->name = $request->name;
        $size->short_name = $request->short_name;
        $size->save();

        return send_msg("Size Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Size $size)
    {
        if(is_null($this->user) || !$this->user->can('size.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $size->delete();
        return send_msg('Delete Success', true, 200);
    }
}
