<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Auth\Passwords\PasswordBroker;
use App\User;
use Auth;
use Validator;
use DB;

class AuthController extends Controller
{
    /**
    * End Point  :- user/login
    * Method     :- Post
    * Parameters :- email, password
    *               device_type (1 for Android, 2 for IOS , 3 web), device_token (Optional)
    */
    public function login(Request $request){

        $input = $request->all();

        $rules = [
          'email'         => 'required|email',
          'password'      => 'required',
          'device_type'   => 'required',
        ];

        $validator = Validator::make($request->all(), $rules );

        if ($validator->fails()) {
            $errors =  $validator->errors()->all();
            return response(['status' => false , 'message' => $errors[0]]);              
        }

         if(!auth()->guard()->attempt(array( 'email' => $input['email'] , 'password' => $input['password'] , 'role_id' => '2'  , 'deleted_at' => NULL ))) {
            return response(['status' => false , 'message' => __('Invalid credientials') ]);       
         } 
        

         $User = User::find(auth()->guard()->id());

         auth::logout();

         if($User->is_active != '1'){
            return response(['status'=>false , 'message'=>__('Your are inactive , Please contact with your administrator')]);
         }

         $User->device_type  = $input['device_type']  ?? NULL;
         $User->device_token = $input['device_token'] ?? NULL;
         $User->update();

         $data['user_id']           = $User->id;
         $data['user_name']         = $User->name      ?? '';
         $data['profile_image']     = $User->profile_image;
         $data['email']             = $User->email     ?? '';
         $data['phone']      = $User->phone     ?? '';
         $data['address']           = $User->address   ?? '';

        return response(['status' => true , 'message' => __('Successfully logged In') , 'data' => $data ]);
    }

    /**
    * End Point  :- user/signup
    * Method     :- Post
    * Parameters :- phone, name, email, password, address,
    *               device_type (1 for Android, 2 for IOS , Web), device_token (Optional)
    */
    public function signup(Request $request){

        $input = $request->all();

        $rules = [
          'phone'     => 'required|min:10|max:10|unique:users,phone,null,id,deleted_at,NULL',
          'name'     => 'required',
          'email'         => 'required|unique:users,email,null,id,deleted_at,NULL',
          'password'      => 'min:8|required',
          'address'       => 'required',
          'device_type'   => 'required'
        ];

        $validator = Validator::make($request->all(), $rules );

        if ($validator->fails()) {
            $errors =  $validator->errors()->all();
            return response(['status' => false , 'message' => $errors[0]]);              
        }

        $insertData = [
           'phone'         => $input['phone'],
           'name'          => $input['name'],
           'email'         => $input['email'],
           'password'      => \Hash::make($input['password']),
           'address'       => $input['address'],
           'device_type'   => $input['device_type'],
           'device_token'  => $input['device_token'] ?? Null,
           'role_id'       => '2'
        ];

        $User = User::insertGetId($insertData);

        if($User){
          $insertData['user_id']   = $User;
          return ['status'=>true,'message'=>'Successfully signed up','data'=>$insertData];
        }
        else{
          return ['status'=>false,'message'=>'Failed to sign up'];
        }
    }

    /**
    * End Point  :- user/get/profile
    * Method     :- Get
    * Parameters :- user_id
    */
    public function getProfile(Request $request){

      $input = $request->all();

      $rules = [
        'user_id' => 'required',
      ];

      $validator = Validator::make($request->all(), $rules );

      if ($validator->fails()) {
          $errors =  $validator->errors()->all();
          return response(['status' => false , 'message' => $errors[0]]);              
      }

      $isUserExist = $this->isUserExist($input['user_id']);

      if(!$isUserExist)
           return response(['status'=>false,'message'=>__('This user does not exist')],401);

       $User = User::find($input['user_id']);

       if($User->is_active != '1'){
          return response(['status'=>false , 'message'=>__('Your are inactive , Please contact with your administrator')],401);
       }

       $data['user_id']           = $User->id;
       $data['name']         = $User->name      ?? '';
       $data['profile_image']     = $User->profile_image;
       $data['email']             = $User->email     ?? '';
       $data['phone']      = $User->phone     ?? '';
       $data['address']           = $User->address   ?? '';

      return response(['status' => true , 'message' => __('Record found') , 'data' => $data ]);
    }

     /**
    * End Point  :- user/update/profile
    * Method     :- Post
    * Parameters :- user_id,phone, name, email, password , address
    *                , profile_image (Optional)
    */
    public function updateProfile(Request $request){

      $input = $request->all();

      $UserId     = $input['user_id'] ?? NULL;

      $rules = [
        'user_id'  => 'required',
        'phone' => 'required|min:10|max:10|unique:users,phone,'.$UserId.',id,deleted_at,NULL',
        'name'    => 'required',
        'email'        => 'required|unique:users,email,'.$UserId.',id,deleted_at,NULL',
        'address' => 'required'
      ];

      $validator = Validator::make($request->all(), $rules );

      if ($validator->fails()) {
          $errors =  $validator->errors()->all();
          return response(['status' => false , 'message' => $errors[0]]);              
      }

      $isUserExist = $this->isUserExist($input['user_id']);

      if(!$isUserExist)
           return response(['status'=>false,'message'=>__('This user does not exist')],401);

       $User = User::find($input['user_id']);

       if($User->is_active != '1'){
          return response(['status'=>false , 'message'=>__('Your are inactive , Please contact with your administrator')],401);
       }

       $fileName = null;
       if ($request->hasFile('profile_image')) {
          $fileName = str_random('10').'.'.time().'.'.request()->profile_image->getClientOriginalExtension();
          request()->profile_image->move(public_path('images/profile/'), $fileName);
        }
       
      $updateData = [
         'phone'         => $input['phone'],
         'name'          => $input['name'],
         'email'         => $input['email'],
         'address'       => $input['address'],
        ];

        if($fileName)
           $updateData['profile_image'] = $fileName;
      
      $User = User::where('id',$UserId)->update($updateData);

      return ['status'=>true,'message'=>__('Successfully updated')];

      if($User)
        return ['status'=>true,'message'=>__('Successfully updated')];
      else
        return ['status'=>false,'message'=>__('Failed to sign up')];
    }

     /**
    * End Point  :- user/change/password
    * Method     :- Post
    * Parameters :- user_id, old_password, new_password
    *
    */
    public function changePassword(Request $request){
        
      $input    = $request->all();

      $rules = [
                'user_id'           => 'required',
                'old_password'      => 'required',
                'new_password'      => 'min:8|required',
               ];

      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
        $errors =  $validator->errors()->all();
        return response(['status' => false , 'message' => $errors[0]] , 200);              
      }

      $isUserExist = $this->isUserExist($input['user_id']);

      if(!$isUserExist)
           return response(['status'=>false,'message'=>__('This user does not exist')],401);

       $User = User::find($input['user_id']);

       if($User->is_active != '1'){
          return response(['status'=>false , 'message'=>__('Your are inactive , Please contact with your administrator')],401);
       }

       if (!(\Hash::check($request->old_password,  $User->password))) {
            return ['status' => false , 'message' => __('Your old password does not matches with the current password  , Please try again')];
       }

       elseif(strcmp($request->old_password, $request->new_password) == 0){
            return ['status' => false , 'message' => __('New password cannot be same as your current password,Please choose a different new password')];
       }

        $User->password = \Hash::make($input['new_password']);
        if($User->update()){
         return response(['status' => true , 'message' => __('Successfully updated')] , 200);
        }
        return response(['status' => false , 'message' => __('Failed to update')] , 200);
    }
  
      /**
    * End Point  :- user/forgot/password
    * Method     :- Post
    * Parameters :- email
    *
    */
    public function forgotPassword(Request $request){
       
      $input    = $request->all();

      $rules = [
                'email' => 'required|email',
               ];

      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
        $errors =  $validator->errors()->all();
        return response(['status' => false , 'message' => $errors[0]] , 200);              
      }

      $User = User::where('email',$input['email'])->whereNull('deleted_at')->first();

      if(empty($User) || is_null($User)){
          return ['status'=>false,'message'=>'This email address does not exist'];
      }

       if($User->is_active != '1'){
        return response(['status'=>false , 'message'=>__('Your are inactive , Please contact with your administrator')],401);
       }

      $token = app(PasswordBroker::class)->createToken($User);
      $data = array(
        'to'     => $input['email'],
        'link'   => url('password/reset/'.$token)
      );

      try {
          \Mail::send('Mails.forgot_password', $data, function ($message) use($data) {
            $message->from( env('MAIL_FROM') , env('MAIL_FROM_NAME') );
            $message->to($data['to'])->subject('Reset password for GR eCommerce account');
          });
          return ['status'=>true,'message'=>'Reset password link sent to you email address'];
      } catch (\Exception $th) {
          return ['status' => false , 'message'=>'Failed to send reset password link'];
      }
      
    }

    /**
     *  Is User Exist
    */
    public function isUserExist($userId){
      $User = DB::table('users')->where('id',$userId)->where('role_id','3')->whereNull('deleted_at')->count();
      if($User)
        return true;
      else
        return false;
    }
}
