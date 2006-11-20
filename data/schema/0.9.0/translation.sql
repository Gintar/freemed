# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2006 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

CREATE TABLE `translation` (
	ttimestamp		TIMESTAMP (14) DEFAULT NOW(),
	tpatient		INT UNSIGNED NOT NULL DEFAULT 0,
	tmodule			VARCHAR (150) NOT NULL,
	tid			INT UNSIGNED NOT NULL,
	tuser			INT UNSIGNED NOT NULL,
	tlanguage		CHAR(10) NOT NULL,
	tcomment		TEXT,
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Define keys

	KEY			( tpatient, tmodule, tid, tlanguage )
);

