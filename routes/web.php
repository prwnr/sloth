<?php

Route::get('/{any}', 'DefaultController@index')->where('any', '.*');