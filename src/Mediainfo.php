<?php

namespace datagutten\descriptionMaker;

use dependcheck;
use DependencyFailedException;
use FileNotFoundException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Mediainfo
{
	/**
	 * @throws DependencyFailedException
	 */
	protected static function dependcheck()
	{
		$dependcheck = new dependcheck();
		$dependcheck->depend('mediainfo');
	}

	/**
	 * @param string $file File name
	 * @param array $args
	 * @return Process
	 * @throws DependencyFailedException|FileNotFoundException
	 */
	protected static function mediainfo(string $file, array $args = []): Process
	{
		if (!file_exists($file))
			throw new FileNotFoundException($file);
		self::dependcheck();

		$process = new Process(array_merge(['mediainfo', $file], $args));
		$process->run();
		if (!$process->isSuccessful())
			throw new ProcessFailedException($process);
		return $process;
	}

	/**
	 * Run mediainfo with plain output, but file name removed
	 * @param string $file
	 * @return string
	 * @throws DependencyFailedException
	 * @throws FileNotFoundException
	 */
	public static function plain(string $file): string
	{
		if (!file_exists($file))
			throw new FileNotFoundException($file);
		self::dependcheck();

		$process = self::mediainfo($file);

		$info = $process->getOutput();
		$info = preg_replace("/Complete name.+\n/", '', $info);
		return preg_replace("/Unique ID.+\n/", '', $info);
	}

	/**
	 * Run mediainfo and format output with spaces and BBCode
	 * @param string $file
	 * @return string
	 * @throws DependencyFailedException
	 * @throws FileNotFoundException
	 */
	public static function pretty(string $file): string
	{
		$process = self::mediainfo($file, ['--Output=XML']);
		$xml = simplexml_load_string($process->getOutput());
		$key_lengths = [];
		$output = [];
		foreach ($xml->{'media'}->{'track'} as $track)
		{
			$output[] = $track->attributes()->{'type'};
			//$output[]=$data['@attributes']['type'];
			$outputkeys[] = 'header';
			foreach ($track as $key => $value)
			{
				if (array_search($key, array('@attributes', 'Unique_ID', 'Complete_name', 'Encoding_settings', 'Color_primaries', 'Transfer_characteristics', 'Matrix_coefficients')) === false)
				{
					$output[] = $value;
					$outputkeys[] = $key;
					$key_lengths[] = strlen($key);
				}
			}
		}

		$maxlen = max($key_lengths); //Find the longest key
		$mediainfo = '';
		foreach ($output as $key => $value)
		{
			if ($outputkeys[$key] != 'header')
				$mediainfo .= str_pad($outputkeys[$key], $maxlen + 5) . ": $value\n";
			else
				$mediainfo .= "\n[b]" . $value . "[/b]\n";
		}
		return $mediainfo;
	}
}