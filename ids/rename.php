<?php declare(strict_types = 1);

process(__DIR__ . '/ddr_pathnorm.tsv');

// program();

function process($pgm_file) {
    $restart_file = __DIR__ . "/rename_restart.php";
    $drive = "Z:/";
    $handle = fopen($pgm_file, "r");
    $restart = @include($restart_file);
    $n = 0;
    while (($row = fgetcsv($handle, null, "\t")) !== FALSE) {
        if ($n++ === 0) continue;
        if ($n < $restart) continue;
        if (count($row) != 3) continue;
        if ($row[0][0] == '#') continue;
        $dir = dirname($drive . $row[1]);
        echo "$row[0]\n";
        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            $error = error_get_last();
            echo $error['message'];
            exit();
        }
        $dir = dirname($drive . $row[2]);
        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            $error = error_get_last();
            echo $error['message'];
            exit();
        }
        if (!@copy($drive . $row[0], $drive . $row[1])) {
            $error = error_get_last();
            echo $error['message'];
            exit();
        }
        if (!@copy($drive . $row[0], $drive . $row[2])) {
            $error = error_get_last();
            echo $error['message'];
            exit();
        }
        file_put_contents($restart_file, "<?php return $n");
    }
    fclose($handle);
}

function program() {
    // load listing
    $listing = file(__DIR__ . '/listing.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // from zotero Json, build: old id => new id
    $idold_idnew = [];
    $handle = fopen(__DIR__ . '/oldid_newid.tsv', "r");
    while (($row = fgetcsv($handle, null, "\t")) !== FALSE) {
        $idold_idnew[$row[0]] = $row[1];
    }
    fclose($handle);
    echo "actuel\tarchive\tiiif\n";
    foreach($listing as $path) {
        $members = explode('/', $path);
        $ext = pathinfo(end($members), PATHINFO_EXTENSION);
        $name = pathinfo(end($members), PATHINFO_FILENAME);
        if (end($members) === '.DS_Store') {
            continue;
        }
        if (end($members) === 'Thumbs.db') {
            continue;
        }
        if ($members[1] == 'ddr-articles') {
            if (count($members) !== 4) {
                echo "$path\t### autre structure\n";
                continue;
            }
            $pos = strrpos($name, '_');
            if ($pos === false) {
                echo "$path\t### pas de page\n";
                continue;
            }
            $idold = substr($name, 0, $pos);
            if (!isset($idold_idnew[$idold])) {
                echo "$path\t### $idold non trouvé dans les URL\n";
                continue;
            }
            $page = str_pad(substr($name, $pos + 1), 3, '0', STR_PAD_LEFT);
            if (!$page || strlen($page) != 3) {
                echo "$path\t### $page, est-ce un no de page ?\n";
                continue;
            }
            $idnew = $idold_idnew[$idold];
            $collection = $members[2];
            // {id}/###.jpg
            echo "$path\tddr-images/$collection/{$idnew}_{$page}.$ext\tiiif/ddr/{$idnew}/{$page}.$ext\n";
        }
        else if ($members[1] == 'ddr-livres') {
            if (count($members) !== 4) {
                echo "$path\t### autre structure\n";
                continue;
            }
            $pos = strrpos($name, '_');
            if ($pos === false) {
                echo "$path\t### pas de page\n";
                continue;
            }
            $idold = substr($name, 0, $pos);
            if (!isset($idold_idnew[$idold])) {
                echo "$path\t### $idold non trouvé dans les URL\n";
                continue;
            }
            $page = str_pad(substr($name, $pos + 1), 3, '0', STR_PAD_LEFT);
            if (!$page || strlen($page) != 3) {
                echo "$path\t### $page, est-ce un no de page ?\n";
                continue;
            }
            $idnew = $idold_idnew[$idold];
            echo "$path\tdepot/{$idnew}/{$idnew}_{$page}.$ext\tiiif/ddr/{$idnew}/{$page}.$ext\n";
        }
    }
}

