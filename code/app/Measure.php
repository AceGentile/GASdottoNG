<?php

namespace app;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\SluggableID;

class Measure extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;
}
