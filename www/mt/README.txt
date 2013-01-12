                          MindTerm 2.4.2

MindTerm is an SSH client written in Java. It supports both version 1
and 2 of the SSH protocol. It can be used as a standalone application,
and applet or as a library with SSH-classes which can be used by other
projects.

This is the binary distribution of MindTerm. The files included in
this zip-file are:

mindterm.jar:	        Full unsigned binary
mindterm-obf.jar:	Full obfuscated unsigned binary
LICENSE.txt:		Current license
README.txt:		This file...
docs/Applet.txt         Instructions for running MindTerm as an applet
docs/Settings.txt       Various settings one can use with MindTerm

The full obfuscated unsigned binary is primarily for using MindTerm
"standalone" (i.e. as any ordinary SSH client) which means you have to
install a Java runtime (JVM) to run it. This binary has been
obfuscated to make it smaller. The unobfuscated binary is what you need
if you want to use the MindTerm classes in your own project.

Depending on your platform the application is started in different
ways. For example in windows when a recent JRE is installed you only
have to double-click the mindterm.jar file and MindTerm will run. On
other platforms it might be something like:

  java -jar mindterm.jar

  java -cp mindterm.jar com.mindbright.application.MindTerm

  java -classpath mindterm.jar com.mindbright.application.MindTerm

  jview /cp:p mindterm.jar com.mindbright.application.MindTerm

For more info on MindTerm and related issues visit
http://www.appgate.com/mindterm
