<?php

//declare the class as final to notify about the place of the class in the code
final class TemplateManager
{
    /**
     * @var data of treatment
     */
    private $_data = [];

    /**
     * Transform template of personnalised template
     * @param  Template $tpl  Template Object
     * @param  array    $data all data for replacement
     * @return Template         complete template
     */
    public function getTemplateComputed(Template $tpl, array $data)
    {
        $this->_data = $data;
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }
        //The clone is not necassary here
        $tpl->subject = $this->_computeSubject($tpl->subject);
        $tpl->content = $this->_computeContent($tpl->content);
        return $tpl;
    }

    /**
     * Manage template subject
     * @param  string $text The template text
     * @return string       the text of the subject
     */
    private function _computeSubject($text)
    {
        //Using ternary
        return isset($this->_data['quote']) ? str_replace(
                '[quote:destination_name]',
                DestinationRepository::getInstance()->getById($this->_data['quote']->destinationId)->countryName,
                $text
            ) : $text;
    }

    /**
     * Manage template content
     * @param  string $text The template text
     * @return string       the text of the subject
     */
    private function _computeContent($text)
    {
        //Best practice to separe instruction. The text could be pass by reference
        $this->_manageUser($text);
        // The condition could go inside if block
        if (isset($this->_data['quote']) && $this->_data['quote'] instanceof Quote){
            $quote               = $this->_data['quote'];
            $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject        = SiteRepository::getInstance()->getById($quote->siteId);
            $destination         = DestinationRepository::getInstance()->getById($quote->destinationId);
            //All of the previous code could be done in 1 line like this
            return str_replace(
                [
                    '[quote:summary_html]',
                    '[quote:summary]',
                    '[quote:destination_name]',
                    '[quote:destination_link]',
                ],[
                    Quote::renderHtml($quoteFromRepository),
                    Quote::renderText($quoteFromRepository),
                    $destination->countryName,
                    $usefulObject->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id,
                ],
                $text
            );
        }
        //The text is containing any replacement. MUST BE returned
        return $text;
    }

    /**
     * Specific function for user management
     * @param  string &$text The text before traitement
     */
    private function _manageUser(&$text)
    {
        $user = isset($this->_data['user']) && ($this->_data['user'] instanceof User) ?
            $this->_data['user'] : ApplicationContext::getInstance()->getCurrentUser();
        $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($user->firstname)), $text);
    }
}
