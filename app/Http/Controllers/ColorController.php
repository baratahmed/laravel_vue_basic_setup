<?php

namespace App\Http\Controllers;

use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ColorController extends Controller
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
        if(is_null($this->user) || !$this->user->can('color.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $colors = Color::query()
                ->with('user:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return ColorResource::collection($colors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('color.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:20',
            'code' => 'required|max:20',
        ]);
        $color = new Color();
        $color->user_id = $request->user()->id;
        $color->name = $request->name;
        $color->code = $request->code;
        $color->save();

        return send_msg("Color Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('color.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Color::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('color.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:20',
            'code' => 'required|max:20',
        ]);

        $color = Color::find($request->id);
        $color->user_id = $request->user()->id;
        $color->name = $request->name;
        $color->code = $request->code;
        $color->save();

        return send_msg("Color Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        if(is_null($this->user) || !$this->user->can('color.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $color->delete();
        return send_msg('Delete Success', true, 200);
    }
}
