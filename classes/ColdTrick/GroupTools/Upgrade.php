<?php

namespace ColdTrick\GroupTools;

class Upgrade {
	
	/**
	 * Set the correct class for the GroupMail subtype
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param mixed  $object supplied object
	 *
	 * @return void
	 */
	public static function setGroupMailClassHandler($event, $type, $object) {
		
		if (get_subtype_id('object', \GroupMail::SUBTYPE)) {
			update_subtype('object', \GroupMail::SUBTYPE, 'GroupMail');
		} else {
			add_subtype('object', \GroupMail::SUBTYPE, 'GroupMail');
		}
	}
}
