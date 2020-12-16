<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Validator;

class ContactController extends Controller
{
    public function index(){
        $contacts = Contact::whereNull('deleted_at')->get();
        if($contacts->toArray())
           return ['status'=>true,'message'=>'Record found','data'=>$contacts];
        else
           return ['status'=>false,'message'=>'Record not found'];
    }

    public function create(Request $request){
       
        $input = $request->all();

        $rules = [
          'name'    => 'required',
          'phone'   => 'required',
          'email'   => 'required',
          'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules );

        if ($validator->fails()) {
            $errors =  $validator->errors()->all();
            return response(['status' => false , 'message' => $errors[0]]);              
        }

        $insertData = [
           'user_id'       => $input['user_id'],
           'name'          => $input['name'],
           'phone'         => $input['phone'],
           'email'         => $input['email']
        ];

        $insertId = Contact::insertGetId($insertData);

        if($insertId){
          $insertData['contact_id']   = $insertId;
          return ['status'=>true,'message'=>'Successfully created','data'=>$insertData];
        }
        else{
          return ['status'=>false,'message'=>'Failed to create'];
        }
    }

    public function show(Request $request){

        $input = $request->all();

        $rules = [
          'contact_id'  => 'required',
          'user_id'     => 'required',
        ];

        $validator = Validator::make($request->all(), $rules );

        if ($validator->fails()) {
            $errors =  $validator->errors()->all();
            return response(['status' => false , 'message' => $errors[0]]);              
        }

        $contact = Contact::where('id',$input['contact_id'])->where('user_id',$input['user_id'])->whereNull('deleted_at')->first();
        if($contact)
           return ['status'=>true,'message'=>'Record found','data'=>$contact];
        else
           return ['status'=>false,'message'=>'Record not found'];
    }

    public function update(Request $request){
       
        $input = $request->all();

        $rules = [
          'contact_id'  => 'required',
          'name'    => 'required',
          'phone'   => 'required',
          'email'   => 'required'
        ];

        $validator = Validator::make($request->all(), $rules );

        if ($validator->fails()) {
            $errors =  $validator->errors()->all();
            return response(['status' => false , 'message' => $errors[0]]);              
        }

        $updateData = [
           'name'          => $input['name'],
           'phone'         => $input['phone'],
           'email'         => $input['email']
        ];

        if(Contact::where('id',$input['contact_id'])->update($updateData)){
          return ['status'=>true,'message'=>'Successfully updated','data'=>$input];
        }
        else{
          return ['status'=>false,'message'=>'Failed to update'];
        }
    }

    public function destroy(Request $request){

        $input = $request->all();

        $rules = [
          'contact_id'  => 'required',
          'user_id'     => 'required',
        ];

        $validator = Validator::make($request->all(), $rules );

        if ($validator->fails()) {
            $errors =  $validator->errors()->all();
            return response(['status' => false , 'message' => $errors[0]]);              
        }

        $contact = Contact::where('id',$input['contact_id'])->where('user_id',$input['user_id'])->whereNull('deleted_at')->first();
        $contact->deleted_at = date('Y-m-d H:i:s');
        if($contact->update())
           return ['status'=>true,'message'=>'Successfully deleted'];
        else
           return ['status'=>false,'message'=>'failed to delete'];
    }
}
