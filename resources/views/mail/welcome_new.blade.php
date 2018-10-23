<h4>Welcome {{ $user->firstname }}</h4>

<p>Your slothful account has been created successfully.</p>
<p>You are now associated with team {{ $team }}</p>
<p>Pleas sing in: <a href="{{ URL::to('/')}}">Sloth</a></p>
<p>Login: {{ $user->email }}</p>
<p>Password: {{ $password }}</p>
<br/><br/>

<hr/>
Sloth
