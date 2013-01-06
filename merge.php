<?php
session_start();

//ini_set('memory_limit', '268435456');

$hash_table = new Hash();

$all_chunks = glob("files/chunks/" . session_id() . "/*");
foreach ($all_chunks as $chunk_file_name) {

	$chunk_file = file_get_contents($chunk_file_name);

	$temp_array = explode("\n", $chunk_file, 2);
	$chunk = $temp_array[1];
	$word_array = explode(' ', $chunk);

	foreach ($word_array as $word) {
		if (strlen(trim($word)) != 0) {
			$hash_table->insert($word, basename($chunk_file_name));
		}
	}
}
$tsv_array = $hash_table->export();
$tsv = implode("\t", $tsv_array);
$tsv = str_replace("\t\n", "\n", $tsv);

file_put_contents("files/merge.tsv", $tsv);

class Hash
{
	private $chunk_array;
	private $word_array;

	public function Hash()
	{
		$this->chunk_array = array();
		$this->word_array = array();
	}

	public function insert($word, $chunk_name)
	{
		if (isset($this->chunk_array[$chunk_name])) {
			if (isset($this->chunk_array[$chunk_name][$word])) {
				$this->chunk_array[$chunk_name][$word] += 1;
			}
			else {
				$this->chunk_array[$chunk_name][$word] = 1;
			}
		}
		else {
			$this->chunk_array[$chunk_name] = array($word => 1);
		}

		if (!in_array($word, $this->word_array)) {
			array_push($this->word_array, $word);
		}
	}

	public function export()
	{
		$print_array = array_values($this->word_array);
		array_unshift($print_array, "Transpose");
		foreach ($this->chunk_array as $chunk_name => $chunk_words) {
			array_push($print_array, "\n" . $chunk_name);
			foreach ($this->word_array as $word) {
				if (isset($chunk_words[$word])) {
					array_push($print_array, $chunk_words[$word]);
				}
				else {
					array_push($print_array, '0');
				}
			}
		}
		array_push($print_array, "\n");

		return $print_array;
	}
}

?>