<?php

namespace App\Models;

use App\Db\Model;

class User extends Model {


	public function isSuperAdmin() {
		return $this->attributes['isSuperAdmin'] == 1;
	}
}