<?php
namespace BranchoTest;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ConfigTester extends \Codeception\Actor
{
    use _generated\ConfigTesterActions;

    /**
     * @return string
     */
    public function getPathToValidConfiguration(): string
    {
    }

    /**
     * @return string
     */
    public function getPathToInValidConfiguration(): string
    {
    }
}
