<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();
        $user = isset($data['user']) && ($data['user'] instanceof User) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();

        if (isset($data['quote']) && $data['quote'] instanceof Quote){
            $quote               = $data['quote'];
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
