if($runtime === NULL || !isset($runtime['digests'])) {
	$runtime['digests'] = thread_digest_count();
	cache_set('runtime', $runtime);
}