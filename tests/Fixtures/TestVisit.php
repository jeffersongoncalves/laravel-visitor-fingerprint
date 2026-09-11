<?php

namespace JeffersonGoncalves\VisitorFingerprint\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestVisit extends Model
{
    public $timestamps = false;

    protected $table = 'test_visits';

    protected $guarded = [];
}
