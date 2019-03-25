<?php

/**
 * Social Media
 *
 * Copyright 2019 by Sterc <modx@sterc.nl>
 */

$package = 'oAuthProvider';

$permissions = [[
    'name'          => 'socialmedia',
    'description'   => 'To view the socialmedia package.',
    'templates'     => ['AdministratorTemplate']
], [
    'name'          => 'socialmedia_admin',
    'description'   => 'To view the socialmedia package, admin part.',
    'templates'     => ['AdministratorTemplate'],
    'policies'      => ['Administrator']
]];

$success = false;

if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx =& $object->xpdo;

            foreach ($modx->getCollection('modAccessPolicyTemplate') as $accessTemplate) {
                foreach ($permissions as $permission) {
                    if (!isset($permission['templates']) || in_array($accessTemplate->get('name'), $permission['templates'])) {
                        $accessPermission = $modx->getObject('modAccessPermission', [
                            'name'      => $permission['name'],
                            'template'  => $accessTemplate->get('id')
                        ]);

                        if (!$accessPermission) {
                            $accessPermission = $modx->newObject('modAccessPermission');

                            if ($accessPermission) {
                                $accessPermission->fromArray(array_merge($permission, [
                                    'template'  => $accessTemplate->get('id'),
                                    'value'     => 1
                                ]));

                                $accessPermission->save();
                            }
                        }
                    }
                }
            }

            foreach ($modx->getCollection('modAccessPolicy') as $accessPolicy) {
                $data = $accessPolicy->get('data');

                foreach ($permissions as $permission) {
                    if (isset($permission['policies'])) {
                        if (in_array($accessPolicy->get('name'), $permission['policies'], true)) {
                            $data[$permission['name']] = true;
                        } else {
                            $data[$permission['name']] = false;
                        }
                    } else {
                        $data[$permission['name']] = true;
                    }
                }

                $accessPolicy->set('data', $data);

                $accessPolicy->save();
            }

            $success = true;

            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $success = true;

            break;
    }
}

return $success;
