`/etc/postfix/main.cf`

# Configure Postfix
* Source 1: https://www.digitalocean.com/community/tutorials/how-to-install-and-setup-postfix-on-ubuntu-14-04
* Source 2: https://help.ubuntu.com/community/PostfixBasicSetupHowto

## To manually edit postfix
    $ sudo nano /etc/postfix/main.cf
    $ sudo postconf -e "myhostname = zorg.ch"
    $ sudo postconf -e "mydestination = localdomain, localhost, localhost.localdomain, localhost, zorg.ch"
    $ sudo postconf -e "mynetworks = 127.0.0.0/8 [::ffff:127.0.0.0]/104 [::1]/128"
    $ sudo postconf -e "home_mailbox = Maildir/"
    $ sudo service postfix restart

# Enable Postfix logging
    $ sudo nano /etc/rsyslog.d/50-default.conf
      > mail.*      -/var/log/mail.log
      > mail.err    -/var/log/mail.err

***
`/var/spool/postfix/public/pickup`

# Restart postfix & resume delivery of existing mails
* Source 1: http://wayilearn.blogspot.ch/2011/11/fixing-error-postdrop-warning-unable-to.html
* Source 2: https://bbs.archlinux.org/viewtopic.php?id=147428
* Source 3: http://serverfault.com/questions/279803/postfix-how-to-retry-delivery-of-mail-in-queue

1. Does /var/spool/postfix/public/pickup exist? If not, create it:
`$ mkfifo /var/spool/postfix/public/pickup`

2. Kill the mail process
`$ ps aux | grep mail`
`$ kill <process-id>`

3. Restart postfix
`$ sudo service postfix restart`

4. Flush the mail queue
`$ sudo postqueue -f`
