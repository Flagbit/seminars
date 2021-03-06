<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This interface needs to be used for hooks concerning the event single view.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Seminars_Interface_Hook_EventSingleView
{
    /**
     * Modifies the event single view.
     *
     * @param Tx_Seminars_Model_Event $event
     *        the event to display in the single view
     * @param Tx_Oelib_Template $template
     *        the template that will be used to create the single view output
     *
     * @return void
     */
    public function modifyEventSingleView(Tx_Seminars_Model_Event $event, Tx_Oelib_Template $template);

    /**
     * Modifies a list row in the time slots list (which is part of the event
     * single view).
     *
     * @param Tx_Seminars_Model_TimeSlot $timeSlot
     *        the time slot to display in the current row
     * @param Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyTimeSlotListRow(Tx_Seminars_Model_TimeSlot $timeSlot, Tx_Oelib_Template $template);
}
