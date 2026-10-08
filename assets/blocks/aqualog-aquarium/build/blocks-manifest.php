<?php
// This file is generated. Do not modify it manually.
return array(
	'aqualog-aquarium' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/aqualog-aquarium',
		'version' => '0.1.0',
		'title' => 'Aqualog: Aquarium',
		'category' => 'widgets',
		'description' => 'Aqualog: Single aquarium block.',
		'example' => array(
			
		),
		'supports' => array(
			'color' => array(
				'background' => false,
				'text' => false
			),
			'html' => false,
			'typography' => array(
				'fontSize' => false
			)
		),
		'textdomain' => 'aqualog',
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php',
		'attributes' => array(
			'postId' => array(
				'type' => 'string',
				'default' => ''
			),
			'postTitle' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	)
);
