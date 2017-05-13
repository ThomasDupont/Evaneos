<?php

class TemplateManager
{

    private $_data = [];
    
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }
        $this->_data = $data;
        $tpl->subject = $this->_computeSubject($tpl->subject);
        $tpl->content = $this->_computeContent($tpl->content);
        return $tpl;
    }

    private function _computeSubject($text)
    {
        return isset($this->_data['quote']) ? str_replace(
                '[quote:destination_name]',
                DestinationRepository::getInstance()->getById($this->_data['quote']->destinationId)->countryName,
                $text
            ) : $text;
    }

    private function _computeContent($text)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();
        $user = isset($this->_data['user']) && ($this->_data['user'] instanceof User) ?
            $this->_data['user'] : $APPLICATION_CONTEXT->getCurrentUser();

        if (isset($this->_data['quote']) && $this->_data['quote'] instanceof Quote){
            $quote               = $this->_data['quote'];
            $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject        = SiteRepository::getInstance()->getById($quote->siteId);
            $destination         = DestinationRepository::getInstance()->getById($quote->destinationId);

            return str_replace(
                [
                    '[quote:summary_html]',
                    '[quote:summary]',
                    '[quote:destination_name]',
                    '[quote:destination_link]',
                    '[user:first_name]'
                ],[
                    Quote::renderHtml($quoteFromRepository),
                    Quote::renderText($quoteFromRepository),
                    $destination->countryName,
                    $usefulObject->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id,
                    ucfirst(mb_strtolower($user->firstname))
                ],
                $text
            );
        }
        return $text;
    }
}
