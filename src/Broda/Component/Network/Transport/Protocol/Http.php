<?php

namespace Broda\Component\Network\Transport\Protocol;

use Broda\Component\Network\Transport\TransportInterface;

/**
 * Classe Http
 *
 */
class Http implements ProtocolInterface
{

    public function getContext(TransportInterface $transport)
    {
        $wrapper = $this->getWrapper();
        return stream_context_create(array(
            $wrapper => array(
                'timeout' => $transport->getTimeout(),
            )
        ));
    }

    public function getWrapper()
    {
        return 'http';
    }

}
