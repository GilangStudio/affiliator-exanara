<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class ProjectCRM extends Model
{
    protected $connection = 'crm';
    protected $table = 'project';

    protected $guarded = ['id'];
}
