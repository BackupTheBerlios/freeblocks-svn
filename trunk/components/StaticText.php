
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class StaticText extends Component
{
	public function __construct()
	{
		parent::__construct();
		$this->addProperty('text', 'Text', BaseComponent::TYPE_TEXT, '', array(
			'lines' => 5
		));
	}

	public function getIcon()
	{
		return 'text.png';
	}

	public function renderComponent()
	{
		$text= ($this->hasProperty('text') && ($this->getPropertyValue('text') != ''))?$this->getPropertyValue('text'):'no text';
		$this->xtpl->assign('CONTENT', $text);
		return parent::renderComponent();
	}
}

?>