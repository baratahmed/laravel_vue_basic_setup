<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\FileManagerResource;
use App\Models\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileManagerController extends Controller
{
    public function upload(Request $request){

        if($request->type == 'product'){
            $fileableType = 'App/Models/Product';
        }
        if($request->hasFile('file')){
            $image = $request->file('file');
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            $img = $manager->read($image);
            $img->save(base_path('public/img/products/'.$name_gen));
            $save_url = 'img/products/'.$name_gen;

            $file = new File();
            if(isset($request->fileable_id)){
                $file->fileable_id = $request->fileable_id;
            }
            $file->fileable_type = $fileableType;
            $file->file_original_name = $image->getClientOriginalName();
            $file->file_name = $save_url;
            $file->extension = $image->getClientOriginalExtension();
            $file->file_size = $image->getSize();
            $file->lastmodified_size = $request->lastModified.'_'.$request->size;
            $file->save();

            return $file->id; 
        }
    }

    public function fetchFiles($fileable_id){
        return FileManagerResource::collection(File::where('fileable_id',$fileable_id)->get());
        // return FileManagerResource::collection($admin->files()->latest()->paginate(21));
    }

    public function destroyFromClient($lastmodified,$size){
        $file = File::where('lastmodified_size',$lastmodified.'_'.$size)->first();
        $id = $file->id;
        if(isset($file) && $file!=""){
            $file->delete();
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$file->file_name);
            }else{
                @unlink($file->file_name);
            }
            if(isset($id)){
                return $id;
            }
            return send_msg("Something went wrong!", false, 200);
        }
    }

    public function destroy($fileId){
        $file = File::find($fileId);
        if(isset($file) && $file!=""){
            $file->delete();
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$file->file_name);
            }else{
                @unlink($file->file_name);
            }
            return send_msg("Successfully Deleted", true, 200);
        }
    }
}
