<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FundFileModel extends BaseModel {
	public function makeDir($fund_id, $dirs) {
		foreach ($dirs as $key => $val) {
			$data['file_id'] = $val['file_id'];
			$data['fund_id'] = $fund_id;
			$data['pid'] = $val['pid'];
			$data['file_name'] = $key;
			$save_data[] = $data;
		}
		return $this->addAll($save_data);
	}
}