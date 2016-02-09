<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Event;

/**
 * Class DmsEvents
 *
 * @package Erichard\DmsBundle\Event
 */
class DmsEvents
{
    /**
     * const NODE_ACCESS
     */
    const NODE_ACCESS = 'dms.node.access';

    /**
     * const NODE_ADD
     */
    const NODE_ADD = 'dms.node.add';

    /**
     * const NODE_CREATE
     */
    const NODE_CREATE = 'dms.node.create';

    /**
     * const NODE_EDIT
     */
    const NODE_EDIT = 'dms.node.edit';

    /**
     * const NODE_UPDATE
     */
    const NODE_UPDATE = 'dms.node.update';

    /**
     * const NODE_DELETE
     */
    const NODE_DELETE = 'dms.node.delete';

    /**
     * const NODE_MANAGE
     */
    const NODE_MANAGE = 'dms.node.manage';

    /**
     * const NODE_RESET_PERMISSION
     */
    const NODE_RESET_PERMISSION = 'dms.node.reset_permission';

    /**
     * const NODE_CHANGE_PERMISSION
     */
    const NODE_CHANGE_PERMISSION = 'dms.node.change_permission';

    /**
     * const DOCUMENT_EDIT
     */
    const DOCUMENT_EDIT = 'dms.document.edit';

    /**
     * const DOCUMENT_LINK
     */
    const DOCUMENT_LINK = 'dms.document.link';

    /**
     * const DOCUMENT_ADD
     */
    const DOCUMENT_ADD = 'dms.document.add';

    /**
     * const DOCUMENT_UPDATE
     */
    const DOCUMENT_UPDATE = 'dms.document.update';

    /**
     * const DOCUMENT_DOWNLOAD
     */
    const DOCUMENT_DOWNLOAD = 'dms.document.download';

    /**
     * const DOCUMENT_DELETE
     */
    const DOCUMENT_DELETE = 'dms.document.delete';

    /**
     * const DOCUMENT_REMOVE_THUMBNAIL
     */
    const DOCUMENT_REMOVE_THUMBNAIL = 'dms.document.remove_thumbnail';
}
