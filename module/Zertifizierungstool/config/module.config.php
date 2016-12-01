<?php
namespace Zertifizierungstool;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
		'controllers' => [
				'factories' => [
						Controller\UserController::class => InvokableFactory::class,
						Controller\KursController::class => InvokableFactory::class,
				],
		],
		
		'router' => [
				'routes' => [
						'user' => [
								'type'    => Segment::class,
								'options' => [
										'route' => '/user[/:action[/:id]]',
										'constraints' => [
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id'     => '[0-9]+',
										],
										'defaults' => [
												'controller' => Controller\UserController::class,
												'action'     => 'index',
										],
								],
						'kurs' => [
								'type'    => Segment::class,
								'options' => [
										'route' => '/kurs[/:action]',
										'constraints' => [
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
										],
										'defaults' => [
												'controller' => Controller\KursController::class,
												'action'     => 'index',
										],
								],
						]
						],
				],
		],
		
		'view_manager' => [
				'template_path_stack' => [
						__DIR__ . '/../view',
				],
		],
];