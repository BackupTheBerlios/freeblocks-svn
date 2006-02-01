
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class BoardComponent extends Component
{

	public function getIcon()
	{
		return 'indicator.gif';
	}

	public function renderComponent()
	{
		$content= "";

		if( $this->hasProperty('_sub') )
		{
			foreach($this->getPropertyValue('_sub') as $post)
			{
				$title= isset($post['title'])?$post['title']:'no title';
				$user= isset($post['user'])?$post['user']:'anonymous';
				$msg= isset($post['msg'])?urldecode($post['msg']):'no message';

				$content.= "
				<div class='post'>
					<div class='title'>{$title}</div>
					<div class='user'>Posted by {$user}</div>
					<div class='text'>P{$msg}</div>
				</div>";
			}
		}

		$this->xtpl->assign('CONTENT', $content);

		return parent::renderComponent();
	}
}

?>