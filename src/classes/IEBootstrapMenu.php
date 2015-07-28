<?php

class IEBootstrapMenu extends EaseTWBNavbar
{

    /**
     * Navigace
     * @var EaseHtmlUlTag
     */
    public $nav = NULL;

    /**
     * Hlavní menu aplikace
     *
     * @param string $name
     * @param mixed  $content
     * @param array  $properties
     */
    public function __construct($name = null, $content = null, $properties = null)
    {
        parent::__construct("Menu", new EaseHtmlImgTag('img/vsmonitoring.png', 'VSMonitoring', 20, 20, array('class' => 'img-rounded')), array('class' => 'navbar-fixed-top'));

        $user = EaseShared::user();
        EaseTWBPart::twBootstrapize();
        if (!$user->getUserID()) {
            $this->addMenuItem('<a href="createaccount.php">' . EaseTWBPart::GlyphIcon('leaf') . ' ' . _('Registrace') . '</a>', 'right');
            $this->addMenuItem(
                '
<li class="divider-vertical"></li>
<li class="dropdown">
<a class="dropdown-toggle" href="login.php" data-toggle="dropdown"><i class="icon-circle-arrow-left"></i> ' . _('Přihlášení') . '<strong class="caret"></strong></a>
<div class="dropdown-menu" style="padding: 15px; padding-bottom: 0px; left: -120px;">
<form method="post" class="navbar-form navbar-left" action="login.php" accept-charset="UTF-8">
<input class="form-control" style="margin-bottom: 15px;" type="text" placeholder="' . _('login') . '" id="username" name="login">
<input class="form-control" style="margin-bottom: 15px;" type="password" placeholder="' . _('Heslo') . '" id="password" name="password">
<!-- input style="float: left; margin-right: 10px;" type="checkbox" name="remember-me" id="remember-me" value="1">
<label class="string optional" for="remember-me"> ' . _('zapamatuj si mne') . '</label -->
<input class="btn btn-primary btn-block" type="submit" id="sign-in" value="' . _('přihlásit') . '">
</form>
</div>', 'right'
            );
        } else {

            $userMenu = '<li class="dropdown" style="width: 120px; text-align: right; background-image: url( ' . $user->getIcon() . ' ) ;  background-repeat: no-repeat; background-position: left center; background-size: 40px 40px;"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $user->getUserLogin() . ' <b class="caret"></b></a>
<ul class="dropdown-menu" style="text-align: left; left: -60px;">
<li><a href="settings.php">' . EaseTWBPart::GlyphIcon('wrench') . '<i class="icon-cog"></i> ' . _('Nastavení') . '</a></li>
';

            if ($user->getSettingValue('admin')) {
                $userMenu .= '<li><a href="overview.php">' . EaseTWBPart::GlyphIcon('list') . ' ' . _('Přehled konfigurací') . '</a></li>';
            }

            $this->addMenuItem($userMenu . '
<li><a href="http://v.s.cz/kontakt.php">' . EaseTWBPart::GlyphIcon('envelope') . ' ' . _('Uživatelská podpora') . '</a></li>
<li class="divider"></li>
<li><a href="logout.php">' . EaseTWBPart::GlyphIcon('off') . ' ' . _('Odhlášení') . '</a></li>
</ul>
</li>
', 'right');
        }
    }

    /**
     * Vypíše stavové zprávy
     */
    public function draw()
    {
        $statusMessages = $this->webPage->getStatusMessagesAsHtml();
        if ($statusMessages) {
            $this->addItem(new EaseHtmlDivTag('StatusMessages', $statusMessages, array('class' => 'well', 'title' => _('kliknutím skryjete zprávy'), 'data-state' => 'down')));
            $this->addItem(new EaseHtmlDiv(null, array('id' => 'smdrag')));
        }
        parent::draw();
    }

}
