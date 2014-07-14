<?php

namespace Broda\Component\Network\Transport\Protocol;

use Broda\Component\Network\Transport\TransportInterface;

/**
 * Interface ProtocolInterface
 *
 */
interface ProtocolInterface
{
    /**
     * @param TransportInterface $transport
     * @return resource From \stream_context_create()
     * @see \stream_context_create()
     */
    public function getContext(TransportInterface $transport);

    /**
     * @return string http, ftp, file, rar, etc...
     */
    public function getWrapper();
}
