<?php

namespace markhuot\CraftQL\GraphQL\Types;

use yii\base\Component;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Craft;
use craft\elements\Entry;

class Query extends Component {

    private $sections;
    private $volumes;
    private $categoryGroups;
    private $assetVolumes;

    function __construct(
        \markhuot\CraftQL\Repositories\Sections $sections,
        \markhuot\CraftQL\Repositories\Volumes $volumes,
        \markhuot\CraftQL\Repositories\CategoryGroup $categoryGroups
    ) {
        $this->sections = $sections;
        $this->volumes = $volumes;
        $this->categoryGroups = $categoryGroups;
    }

    function getType() {
        // var_dump(\yii::$container);
        // die;
        // var_dump('1');

        // $this->tagGroups->loadAllGroups();
        // $this->categoryGroups->loadAllGroups();
        $this->sections->loadAllSections();
        // $this->assetVolumes->loadAllVolumes();

        $queryTypeConfig = [
            'name' => 'Query',
            'fields' => [
                'helloWorld' => [
                    'type' => Type::string(),
                    'resolve' => function ($root, $args) {
                      return 'Welcome to GraphQL! You now have a fully functional GraphQL endpoint.';
                    }
                ]
            ],
            'types' => [],
        ];

        $queryTypeConfig['fields']['entries'] = [
            'type' => Type::listOf(\markhuot\CraftQL\GraphQL\Types\Entry::interface()),
            'description' => 'Entries from the craft interface',
            'args' => \markhuot\CraftQL\GraphQL\Types\Section::args(),
            'resolve' => function ($root, $args) {
                $criteria = \craft\elements\Entry::find();
                foreach ($args as $key => $value) {
                    $criteria = $criteria->{$key}($value);
                }
                return $criteria->all();
            }
        ];

        // foreach ($this->sections->loadedSections() as $handle => $sectionType) {
        //     $sectionType = $this->sections->getSection($handle);
        //     $isSingle = $sectionType->config['type'] == 'single';
            
        //     $type = \markhuot\CraftQL\GraphQL\Types\Entry::interface();
        //     // if (count($sectionType->config['entryTypes']) == 1) {
        //     //     $type = $sectionType->config['entryTypes'][0];
        //     // }

        //     // var_dump($sectionType);
        //     // die;

        //     $queryTypeConfig['fields'][$handle] = [
        //         'type' => $isSingle ? $sectionType : Type::listOf($sectionType),
        //         'description' => 'Entries from the '.$handle.' section',
        //         'args' => \markhuot\CraftQL\GraphQL\Types\Section::args(),
        //         'resolve' => function ($root, $args) use ($handle, $isSingle) {
        //             $criteria = \craft\elements\Entry::find();
        //             $criteria = $criteria->section($handle);
        //             foreach ($args as $key => $value) {
        //                 $criteria = $criteria->{$key}($value);
        //             }
        //             return $isSingle ? $criteria->one() : $criteria->all();
        //         }
        //     ];
        // }

        // foreach ($this->categoryGroups->loadedGroups() as $handle => $group) {
        //     $queryTypeConfig['fields'][$handle] = [
        //         'type' => Type::listOf($group),
        //         'resolve' => function ($root, $args) use ($handle) {
        //             $criteria = \craft\elements\Entry::find();
        //             $criteria = $criteria->group($handle);
        //             return $criteria->find();
        //         },
        //     ];
        // }

        return new ObjectType($queryTypeConfig);
    }

    function getTypes() {
        $this->volumes->loadAllVolumes();
        $this->categoryGroups->loadAllGroups();

        return array_merge(
            $this->volumes->getAllVolumes(),
            $this->categoryGroups->getAllGroups(),
            \markhuot\CraftQL\GraphQL\Types\EntryType::all()
        );
    }

}