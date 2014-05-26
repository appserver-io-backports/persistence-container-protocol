<?php

/**
 * TechDivision\PersistenceContainerProtocol\RemoteMethodCallParser
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainerProtocol
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainerClient
 * @link      http://www.appserver.io
 */

namespace TechDivision\PersistenceContainerProtocol;

use TechDivision\WebServer\Sockets\SocketInterface;

/**
 * This is a parser for a native persistence container remote method call.
 *
 * A Remote method call must have the following format:
 *
 * <METHOD> <CONTENT-LENGTH> <PROTOCOL>/<VERSION>\r\n
 * <CONTENT>
 *
 * for example:
 *
 * INVOKE 12 RMC/1.0
 * czoxOiIxIjs=
 *
 * @category  Library
 * @package   TechDivision_PersistenceContainerProtocol
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_PersistenceContainerProtocol
 * @link      http://www.appserver.io
 */
class RemoteMethodCallParser
{

    /**
     * Parses the header of a remote method call.
     *
     * @param string $line The header line to parse
     *
     * @return integer The content length to parse
     * @throws \TechDivision\RemoteMethodProtocol\RemoteMethodCallException
     */
    public function parseHeader($line)
    {

        // parse the header line with
        list ($remoteMethod, $contentLength, $protocolVersion) = explode(' ', trim($line));

        // parse protocol and version
        list ($protocol, $version) = explode('/', $protocolVersion);

        // check if protocol and version are valid
        if ($protocol !== RemoteMethodProtocol::PROTOCOL && $version !== RemoteMethodProtocol::VERSION) {
            throw new RemoteMethodCallException(sprintf('Protocol %s not supported', $protocolVersion));
        }

        // check the request method
        switch ($remoteMethod) {

            case RemoteMethodProtocol::REMOTE_METHOD_INVOKE:
                return (integer) $contentLength;
                break;

            case RemoteMethodProtocol::REMOTE_METHOD_RESULT:
                return (integer) $contentLength;
                break;

            default:
                throw new RemoteMethodCallException(sprintf('Found invalid remote method %s', $remoteMethod));
                break;

        }
    }

    /**
     * Parses the request body and tries to unpack the remote method
     * instance from it.
     *
     * @param TechDivision\Server\Sockets\SocketInterface $connection    The package remote method instance
     * @param integer                                     $contentLength The content lenght to read
     *
     * @return object The unpacked remote method object/result
     */
    public function parseBody(SocketInterface $connection, $contentLength)
    {
        $rawResponse = stream_get_contents($connection->getConnectionResource(), $contentLength);
        return RemoteMethodProtocol::unpack($rawResponse);
    }
}
