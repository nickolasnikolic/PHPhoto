#!/bin/bash
# touchDirs.sh
#
# Prepare directories using touch(1).  The directories will all have
# a similar (nearly-identical) timestamp, thus they'll sort by name
# when they're presented as Qdig gallery directories.
#
# Explanation:
# There's a Qdig setting to sort directories by name, but if
# you sort them by time-since-modified, (the default setting)
# this alphabetizes the listing.  When you create new directories,
# they will appear before the alpha-sorted ones.

find . -maxdepth 1 -type d -print0 |xargs -t -0 touch

# Qdig project SourceForge.net CVS ID
# $Id: touchDirs.sh,v 1.2 2004/04/07 05:00:44 haganfox Exp $
# vim:set tabstop=4 shiftwidth=4 :
