<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;

class WelcomeMail extends Mailable
{
    public $user;
    public $password;
    public $from;

    /**
     * WelcomeMail constructor.
     *
     * @param $user
     * @param $password
     * @param $from
     */
    public function __construct(User $user, string $password)
    {
        $from = [
            'address' => !blank(Auth::user()->email) ? Auth::user()->email : config('mail.from.address'),
            'name' => !blank(Auth::user()->team->name) ? Auth::user()->email : config('mail.from.name')
        ];

        $this->user = $user;
        $this->password = $password;
        $this->from = $from;
    }


    /**
     * Pass data to welcome mail
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(['address' => $this->from['address'], 'name' => $this->from['name']])
            ->view('mail.welcome');
    }

}
