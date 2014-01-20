<?php
namespace Perry\Representation\Eve\v1;

use Perry\Representation\Base;

class TournamentTeamMember extends Base
{
    public $self;

    public $alliance;

    /**
     * @param array|object $self
     */
    public function setSelf($self)
    {
        $this->self = new Reference($self, "vnd.ccp.eve.TournamentTeamMember-v1");
    }

    /**
     * @param array|object $alliance
     */
    public function setAlliance($alliance)
    {
        $this->alliance = new Reference($alliance, "Dear CCP please document this representation");
    }
}
