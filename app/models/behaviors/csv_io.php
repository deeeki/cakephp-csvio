<?php
/**
 * Behavior for CakePHP supports CSV import/export.
 *
 * @author	deeeki <deeeki@gmail.com>
 */
class CsvIoBehavior extends ModelBehavior {
	private $__config;

	public function setup(&$Model, $config = array()) {
		$this->__config[$Model->alias]['importDir'] = CONFIGS . 'csv' . DS;
		$this->__config[$Model->alias]['exportDir'] = TMP . 'csv' . DS;

		if ($config && is_array($config)) {
			foreach ($config as $key => $value) {
				if ($key === 'importDir' || $key === 'exportDir') {
					$this->__config[$Model->alias][$key] = $value;
				}
			}
		}
	}

	public function importCsv(&$Model, $isUpdate = true) {
		$table = Inflector::tableize($Model->name);
		$file = $this->__config[$Model->alias]['importDir'] . $table . '.csv';

		if (!file_exists($file)) {
			throw new Exception('File not found. : ' . $file);
		}

		if (!$isUpdate) {
			$Model->query('TRUNCATE TABLE ' . $table);
		}

		$buf = mb_convert_encoding(file_get_contents($file), 'UTF-8', 'SJIS-win');
		$fp = tmpfile();
		fwrite($fp, $buf);
		rewind($fp);

		$count = 0;
		while($cols = fgetcsv($fp)) {
			//skip headers
			if (trim($cols[0]) == 'id') {
				continue;
			}

			if (!$Model->findById($cols[0])) {
				$Model->create();
			}

			$data = array();
			$idx = 0;
			foreach ($Model->_schema as $field => $schema) {
				$data[$field] = $cols[$idx++];
			}

			$Model->save($data);
			$count++;
		}
		fclose($fp);

		return $count;
	}

	public function exportCsv(&$Model) {
		$table = Inflector::tableize($Model->name);
		$file = $this->__config[$Model->alias]['exportDir'] . $table . '.csv';

		if (!is_writable($this->__config[$Model->alias]['exportDir'])) {
			throw new Exception('Permission denied or directory not found. : ' . $this->__config[$Model->alias]['exportDir']);
		}

		$rows = array();
		$rows[] = array_keys($Model->_schema);//headers

		$data = $Model->query('SELECT * FROM ' . $table);
		foreach ($data as $record) {
			$rows[] = $record[$table];
		}

		mb_convert_variables('SJIS-win', 'UTF-8', $rows);
		$fp = fopen($file, 'w');
		foreach ($rows as $cols) {
		    fputcsv($fp, $cols);
		}
		fclose($fp);

		return count($data);
	}
}
