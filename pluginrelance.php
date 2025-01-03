<?php

$zipName = $argv[2] ?? basename( __DIR__ );
$version = $argv[1] ?? date( 'd.m.Y' );

// Get zip ignore files
$pluginignore = file_get_contents( '.pluginignore' );
$pluginignore = explode( PHP_EOL, $pluginignore );
$pluginignore = array_map( 'realpath', $pluginignore );
$pluginignore = array_filter( $pluginignore );

// Get real path for our folder
$rootPath = realpath( './' );

// Initialize archive object
$zip = new ZipArchive();
$zip->open( "./dist/{$zipName}_v{$version}.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE );
$zip->addEmptyDir( $zipName );

// Create directory iterator
$dir = new DirectoryIterator( $rootPath );

foreach ( $dir as $name => $file ) {
	if ( $file->isDot() ) {
		continue;
	}

	$filePath     = $file->getRealPath();
	$relativePath = substr( $filePath, strlen( $rootPath ) + 1 );
	$relativePath = str_replace( '\\', '/', $relativePath );

	// Check to ignore paths
	foreach ( $pluginignore as $index => $path ) {
		$realPath = realpath( $path );

		if ( strpos( $filePath, $realPath ) !== false ) {
			continue 2;
		}
	}

	// Add to zip
	if ( ! in_array( $relativePath, array( '', '.', '..' ) ) ) {
		if ( $file->isDir() ) {
			// Create recursive directory iterator
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $filePath ),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ( $files as $dirName => $dirFile ) {
				$dirFilePath         = $dirFile->getRealPath();
				$dirFilerelativePath = substr( $dirFilePath, strlen( $filePath ) + 1 );
				$dirFilerelativePath = str_replace( '\\', '/', $dirFilerelativePath );

				if ( ! in_array( $dirFilerelativePath, array( '', '.', '..' ) ) ) {
					if ( $dirFile->isDir() ) {
						$zip->addEmptyDir( "$zipName/$relativePath/$dirFilerelativePath" );
					} elseif ( ! $dirFile->isDir() ) {
						$zip->addFile( $dirFilePath, "$zipName/$relativePath/$dirFilerelativePath" );
					}
				}
			}
		} elseif ( ! $file->isDir() ) {
			$zip->addFile( $filePath, "$zipName/$relativePath" );
		}
	}
}

// Zip archive will be created only after closing object
$zip->close();
