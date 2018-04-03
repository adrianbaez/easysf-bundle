<?php

namespace AdrianBaez\Bundle\EasySfBundle\Event;

/**
 * EntityEvents
 */
class EntityEvents
{
    /**
    * @var string PRE_CREATE
    */
    const PRE_CREATE = 'adrian_baez_easy_sf.preCreate';

    /**
    * @var string POST_CREATE
    */
    const POST_CREATE = 'adrian_baez_easy_sf.postCreate';

    /**
    * @var string PRE_LOAD_LIST
    */
    const PRE_LOAD_LIST = 'adrian_baez_easy_sf.preLoadList';

    /**
    * @var string POST_LOAD_LIST
    */
    const POST_LOAD_LIST = 'adrian_baez_easy_sf.postLoadList';

    /**
    * @var string PRE_LOAD
    */
    const PRE_LOAD = 'adrian_baez_easy_sf.preLoad';

    /**
    * @var string POST_LOAD
    */
    const POST_LOAD = 'adrian_baez_easy_sf.postLoad';

    /**
     * @var string PRE_SAVE
     */
    const PRE_SAVE = 'adrian_baez_easy_sf.preSave';

    /**
     * @var string POST_SAVE
     */
    const POST_SAVE = 'adrian_baez_easy_sf.postSave';

    /**
     * @var string PRE_DELETE
     */
    const PRE_DELETE = 'adrian_baez_easy_sf.preDelete';

    /**
     * @var string POST_DELETE
     */
    const POST_DELETE = 'adrian_baez_easy_sf.postDelete';
}
