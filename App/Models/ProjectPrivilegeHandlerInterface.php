<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/25/18
 * Time: 10:42 AM
 */

namespace ADACT\App\Models;


interface ProjectPrivilegeHandlerInterface {
    /* Project types: beware of constant namings!! */
    /** New project */
    const PT_NEW     = 1;
    /** Pending project */
    const PT_PENDING = 2;
    /** regular project */
    const PT_REGULAR = 3;
    /** Last project: combination of regular project and last project */
    const PT_LAST    = self::PT_NEW;

    /* Edit modes */
    /** Process from the beginning */
    const EM_INIT_FROM_INIT = 0;
    /** Process from absent word */
    const EM_INIT_FROM_AW   = 1;
    /** Process from distance matrix */
    const EM_INIT_FROM_DM   = 2;

    /* Result types */
    /** Project was successful */
    const RT_SUCCESS = 8;
    /** Project is pending */
    const RT_PENDING = 9;
    /** Project has failed */
    const RT_FAILED  = 10;
    /** Project is cancelled */
    const RT_CANCELLED = 11;
}