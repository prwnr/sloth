<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;

/**
 * Class WelcomeMail
 * @package App\Mail
 */
class WelcomeMail extends Mailable
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $team;

    /**
     * WelcomeMail constructor.
     *
     * @param User $user
     * @param string $password
     */
    public function __construct(User $user, ?string $password)
    {
        $from = [
            'address' => !blank(Auth::user()->email) ? Auth::user()->email : config('mail.from.address'),
            'name' => !blank(Auth::user()->team->name) ? Auth::user()->email : config('mail.from.name')
        ];

        $this->user = $user;
        $this->team = $user->team->name;
        $this->password = $password;
        $this->from = $from;
    }


    /**
     * Pass data to welcome mail
     *
     * @return $this
     */
    public function build(): self
    {
        if ($this->password) {
            return $this->from([
                'address' => $this->from['address'],
                'name' => $this->from['name']
            ])->view('mail.welcome_new');
        }

        return $this->from([
            'address' => $this->from['address'],
            'name' => $this->from['name']
        ])->view('mail.welcome_again');
    }

}
