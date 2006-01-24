
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class BoardComponent extends Component
{
	public function renderComponent()
	{
		$content= "";

		foreach($this->_xml_subnodes as $node)
		{
			$title= $this->getFirstNonEmpty($node->getAttribute('title'), 'no title');
			$user= $this->getFirstNonEmpty($node->getAttribute('user'), 'anonymous');
			$msg= $this->getFirstNonEmpty($node->getAttribute('msg'), 'no message');

			$content.= "
			<div class='post'>
				<div class='title'>{$title}</div>
				<div class='user'>Posted by {$user}</div>
				<div class='text'>{$msg}</div>
			</div>";
		}

		$this->xtpl->assign('CONTENT', $content);

		return parent::renderComponent();
	}
}

?>