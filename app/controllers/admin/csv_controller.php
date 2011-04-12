<?php
/**
 * CsvIoBehavior sample controller.
 *
 * @author	deeeki <deeeki@gmail.com>
 */
class CsvController extends AppController {
	var $uses = array();
	var $autoRender = false;

	public function admin_index() {
		App::import('Helper', 'Html');
		$html = new HtmlHelper();

		echo $html->link('import_all') . "<br />\n";
		foreach(glob(CONFIGS . 'csv' . DS . '*.csv') as $file) {
			$table = basename($file, '.csv');
			echo $html->link($table, 'import/' . $table) . "<br />\n";
		}
		echo "<br />\n" . $html->link('export_all') . "<br />\n";
	}

	public function admin_import($table) {
		$model = Inflector::classify($table);
		$this->loadModel($model);
		$this->$model->Behaviors->attach('CsvIo');
		$ret = $this->$model->importCsv();

		echo '[' . $model . '] ' . $ret . ' records imported.' . "<br />\n";
	}

	public function admin_import_all() {
		foreach(glob(CONFIGS . 'csv' . DS . '*.csv') as $file) {
			$model = Inflector::classify(basename($file, '.csv'));
			$this->loadModel($model);
			$this->$model->Behaviors->attach('CsvIo');
			$ret = $this->$model->importCsv();

			echo '[' . $model . '] ' . $ret . ' records imported.' . "<br />\n";
		}
	}

	public function admin_export_all() {
		App::import('Model', 'ConnectionManager');
		$db =& ConnectionManager::getDataSource('default');
		$tables = $db->listSources();

		foreach($tables as $table) {
			$model = Inflector::classify($table);
			$this->loadModel($model);
			$this->$model->Behaviors->attach('CsvIo');
			$ret = $this->$model->exportCsv();

			echo '[' . $model . '] ' . $ret . ' records exported.' . "<br />\n";
		}
	}
}
