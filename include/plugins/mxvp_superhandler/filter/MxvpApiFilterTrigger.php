<?php

//Filter Performs a api Call After the ticket has been created

class MxvpApiAfterFilterTrigger extends TriggerAction{

    static $type = 'mxvpAPIAfter'; //Do not change this or all filter rules will be lost :(
    static $name = "MIXvoip Api (After Create)";

    function apply(&$ticket, array $info){
        $config = $this->getConfiguration();
        $type = SuperhandlerPlugin::FILTERAPIAFTER;
        $data = array(
            'type'=>$type,
            'info'=>$info,
            'config'=>$config,
        );
        Signal::send('mxvp_filter',$data);
    }

    function getConfigurationOptions(){
        $desc="Call a API Link after the ticket has been created<br>Variables:
        <ul>
        <li>
        <i>%%USERMAIL%%</i>: E-mail of the Ticketcreator
        </li>
        <li>
        <i>%%NAME%%</i>: Name of the Ticketcreator
        </li>
        <li>
        <i>%%SUBJECT%%</i>: Subject of the Ticket
        </li>
        <li>
        <i>%%BODY%%</i>: Body of the Ticket (in base64)
        </li>
        <li>
        <i>%%DEP%%</i>: Department of the Ticket
        </li>
        <li>
        <i>%%TICKETID%%</i>: Database-id of the Ticket
        </li>
        <li>
        <i>%%TICKETNO%%</i>: Ref-number of the Ticket
        </li>
        <li>
        <i>%%LINK%%</i>: Link to the Ticket
        </li>
        </ul><hr>
        Response (when not ignored):<br>
        JSON with one or more of the following:
        \"agent\":\"username of the agent to assign the ticket\"<br>
        <hr>";
        return array(
            '' => new FreeTextField(array(
                'configuration'=>array(
                    'content'=>$desc
                )
            )),
            'url' => new TextboxField(array(
                'label' => 'API Link',
                'required' => true,
                'configuration' => array(
                    'placeholder' => 'Api Filter',
                    'size' => 80, 'length' => 1000,
                ),
                'validators'=> function($self, $value) {
                    if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                    }else{
                        $self->addError('Not a valid URL');
                    }
                },
            )),
            'reqType' => new ChoiceField(array(
                'label' => 'Request Type',
                'choices'=>array('GET'=>'GET','POST'=>'POST'),
                'default' => 'GET',
            )),
            'headers' => new TextareaField(array(
                'label' => 'HTTP Headers (one per line)',
                'configuration' => array(
                    'html'=>false,
                    'placeholder' => 'HTTP Headers',
                    'size' => 100, 'length' => 1000,
                ),
            )),
            'postFields' => new TextareaField(array(
                'label' => 'Post Fields',
                'configuration' => array(
                    'html'=>false,
                    'placeholder' => 'Data to send via Post',
                    'size' => 100, 'length' => 1000,
                ),
            )),
            'useResponse'=> new ChoiceField(array(
                'label' => 'Use Response',
                'choices'=>array(0=>'Ignore',1=>'Use'),
                'default' => 0,
            )),
        );
    }
}

class MxvpApiBeforeFilterTrigger extends TriggerAction{

    static $type = 'mxvpAPIBefore';
    static $name = "MIXvoip Api (Before Create)";

    function apply(&$ticket, array $info){
        $config = $this->getConfiguration();
    }

    function getConfigurationOptions(){
        return array(
            'url' => new TextboxField(array(
                'label' => 'API Link',
                'required' => true,
                'configuration' => array(
                    'placeholder' => 'Api Filter',
                    'size' => 80, 'length' => 1000,
                ),
                'validators'=> function($self, $value) {
                    if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                    }else{
                        $self->addError('Not a valid URL');
                    }
                },
            )),
            'reqType' => new ChoiceField(array(
                'label' => 'Request Type',
                'choices'=>array('GET'=>'GET','POST'=>'POST'),
                'default' => 'GET',
            )),
            'headers' => new TextareaField(array(
                'label' => 'HTTP Headers (one per line)',
                'configuration' => array(
                    'html'=>false,
                    'placeholder' => 'HTTP Headers',
                    'size' => 100, 'length' => 1000,
                ),
            )),
            'postFields' => new TextareaField(array(
                'label' => 'Post Fields',
                'configuration' => array(
                    'html'=>false,
                    'placeholder' => 'Data to send via Post',
                    'size' => 100, 'length' => 1000,
                ),
            )),
        );
    }
}