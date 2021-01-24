<?Php

use datagutten\AudioMetadata\AudioMetadata;
use datagutten\descriptionMaker\EAClogBBCode;
use datagutten\descriptionMaker\MusicBrainzDescription;
use datagutten\tools\files\files;
use OrpheusNET\Logchecker\Logchecker;

require __DIR__.'/loader.php';

$mb=new MusicBrainzDescription;

try {
    $file = files::first_file($argv[1], ['flac']);
    $metadata = AudioMetadata::read_metadata($file);
    $desc = $mb->build_description($metadata);
}
catch (FileNotFoundException $e) {
    die($e->getMessage());
}
catch (datagutten\musicbrainz\exceptions\MusicBrainzException $e) {
    die('Error from MusicBrainz: '.$e->getMessage());
}

if($desc===false)
	echo $mb->error."\n";
else
{
    $info=pathinfo($argv[1]);
    $file_description = $info['dirname'].'/'.$info['filename'].'.txt';
    $file_log = files::first_file($argv[1], ['log']);
    if(!empty($file_log))
    {
        $logchecker = new Logchecker();
        $logchecker->new_file($file_log);
        list($score, $details, $checksum_state, $log_text) = $logchecker->parse();

        $log_bbcode = EAClogBBCode::rewrite($log_text);
        $desc .= sprintf("\n\nLog score: %d\n[spoiler]%s[/spoiler]", $score, $log_bbcode);

    }

    file_put_contents($file_description,$desc); //Write the complete description to a file
    printf("Description saved as %s\n", $file_description);
}

