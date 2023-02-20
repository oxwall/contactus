<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Admin page
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_plugins.contactus.controllers
 * @since 1.0
 */
class CONTACTUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function init()
    {
        parent::init();

        $handler = OW::getRequestHandler()->getHandlerAttributes();
        $menus = array();

        $dept = new BASE_MenuItem();
        $dept->setLabel(OW::getLanguage()->text('contactus', 'menu_dept_label'));
        $dept->setUrl(OW::getRouter()->urlForRoute('contactus.admin'));
        $dept->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'dept');
        $dept->setKey('dept');
        $dept->setIconClass('ow_ic_mail');
        $dept->setOrder(0);
        $menus[] = $dept;

        $sender = new BASE_MenuItem();
        $sender->setLabel(OW::getLanguage()->text('contactus', 'menu_sender_label'));
        $sender->setUrl(OW::getRouter()->urlForRoute('contactus.admin_sender'));
        $sender->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'sender');
        $sender->setKey('sender');
        $sender->setIconClass('ow_ic_mail');
        $sender->setOrder(1);
        $menus[] = $sender;

        $this->addComponent('menu', new BASE_CMP_ContentMenu($menus));
    }

    public function dept()
    {
        $this->setPageTitle(OW::getLanguage()->text('contactus', 'admin_dept_title'));
        $this->setPageHeading(OW::getLanguage()->text('contactus', 'admin_dept_heading'));
        $contactEmails = array();
        $deleteUrls = array();
        $contacts = CONTACTUS_BOL_Service::getInstance()->getDepartmentList();
        foreach ( $contacts as $contact )
        {
            /* @var $contact CONTACTUS_BOL_Department */
            $contactEmails[$contact->id]['name'] = $contact->id;
            $contactEmails[$contact->id]['email'] = $contact->email;
            $contactEmails[$contact->id]['label'] = CONTACTUS_BOL_Service::getInstance()->getDepartmentLabel($contact->id);
            $deleteUrls[$contact->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $contact->id));
        }
        $this->assign('contacts', $contactEmails);
        $this->assign('deleteUrls', $deleteUrls);

        $form = new Form('add_dept');
        $this->addForm($form);

        $fieldEmail = new TextField('email');
        $fieldEmail->setRequired();
        $fieldEmail->addValidator(new EmailValidator());
        $fieldEmail->setInvitation(OW::getLanguage()->text('contactus', 'label_invitation_email'));
        $fieldEmail->setHasInvitation(true);
        $form->addElement($fieldEmail);

        $fieldLabel = new TextField('label');
        $fieldLabel->setRequired();
        $fieldLabel->setInvitation(OW::getLanguage()->text('contactus', 'label_invitation_label'));
        $fieldLabel->setHasInvitation(true);
        $form->addElement($fieldLabel);

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('contactus', 'form_add_dept_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                CONTACTUS_BOL_Service::getInstance()->addDepartment($data['email'], $data['label']);
                $this->redirect();
            }
        }
    }

    public function sender()
    {
        $this->setPageTitle(OW::getLanguage()->text('contactus', 'admin_sender_title'));
        $this->setPageHeading(OW::getLanguage()->text('contactus', 'admin_sender_heading'));

        $senderEmail = CONTACTUS_BOL_Service::getInstance()->getSender();

        $form = new Form('set_sender');
        $this->addForm($form);

        $fieldEmail = new TextField('email');
        $fieldEmail->setValue($senderEmail);
        $fieldEmail->setLabel(OW::getLanguage()->text('contactus', 'sender_email_label'));
        $fieldEmail->setDescription(OW::getLanguage()->text('contactus', 'sender_email_desc'));
        $fieldEmail->addValidator(new EmailValidator());
        $fieldEmail->setInvitation(OW::getLanguage()->text('contactus', 'label_invitation_email'));
        $fieldEmail->setHasInvitation(true);
        $form->addElement($fieldEmail);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('contactus', 'form_save_sender_btn'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                CONTACTUS_BOL_Service::getInstance()->saveSender($data['email']);
                OW::getFeedback()->info(OW::getLanguage()->text('contactus', 'sender_email_saved'));
                $this->redirect();
            }
        }
    }

    public function delete( $params )
    {
        if ( isset($params['id']) )
        {
            CONTACTUS_BOL_Service::getInstance()->deleteDepartment((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('contactus.admin'));
    }
}
