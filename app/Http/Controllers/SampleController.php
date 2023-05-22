<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class SampleController extends Controller
{
    /**
     * Show the login view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('login');
    }

    /**
     * Show the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function registration()
    {
        return view('registration');
    }

    /**
     * Validate and process the registration form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validateRegistration(Request $request)
    {
        $validator = $this->validateRegistrationData($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->createUser($request->all());

        return redirect('login')->with('success', 'Registration Completed, now you can login');
    }

    /**
     * Validate and process the login form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validateLogin(Request $request)
    {
        $validator = $this->validateLoginData($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($this->attemptLogin($request->only('email', 'password'))) {
            $this->updateUserToken(Auth::id());
            return redirect('dashboard');
        }

        return redirect('login')->with('success', 'Login details are not valid');
    }

    /**
     * Show the dashboard view.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        if (Auth::check()) {
            return view('dashboard');
        }

        return redirect('login')->with('success', 'You are not allowed to access');
    }

    /**
     * Log out the user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect('login');
    }

    /**
     * Show the user profile view.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function profile()
    {
        if (Auth::check()) {
            $data = User::where('id', Auth::id())->get();
            return view('profile', compact('data'));
        }

        return redirect("login")->with('success', 'You are not allowed to access');
    }

    /**
     * Validate and update the user profile details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validateProfile(Request $request)
    {
        $validator = $this->validateProfileData($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userImage = $request->hidden_user_image;

        if ($request->user_image != '') {
            $userImage = $this->saveUserImage($request->user_image);
        }

        $user = User::find(Auth::id());
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->password != '') {
            $user->password = Hash::make($request->password);
        }

        $user->user_image = $userImage;
        $user->save();

        return redirect('profile')->with('success', 'Profile Details Updated');
    }

    /**
     * Validate the registration form data.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateRegistrationData(array $data)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];

        return validator($data, $rules);
    }

    /**
     * Create a new user.
     *
     * @param  array  $data
     * @return void
     */
    private function createUser(array $data)
    {
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }

    /**
     * Validate the login form data.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateLoginData(array $data)
    {
        $rules = [
            'email' => 'required',
            'password' => 'required'
        ];

        return validator($data, $rules);
    }

    /**
     * Attempt to log in the user.
     *
     * @param  array  $credentials
     * @return bool
     */
    private function attemptLogin(array $credentials)
    {
        return Auth::attempt($credentials);
    }

    /**
     * Update the user's token.
     *
     * @param  int  $userId
     * @return void
     */
    private function updateUserToken($userId)
    {
        $token = md5(uniqid());
        User::where('id', $userId)->update(['token' => $token]);
    }

    /**
     * Validate the profile form data.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateProfileData(array $data)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'user_image' => 'image|mimes:jpg,png,jpeg|max:2048|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000'
        ];

        return validator($data, $rules);
    }

    /**
     * Save the user's image.
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @return string
     */
    private function saveUserImage($image)
    {
        $userImage = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $userImage);
        return $userImage;
    }
}

