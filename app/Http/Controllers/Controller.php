<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Mon-Dataprev e-Consignado API",
 *      description="API de integração Mon-Dataprev com o sistema e-Consignado para operações de consignado CTPS.",
 *      @OA\Contact(
 *          email="gabriel.gomes@outlook.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="dataprevAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="token",
 *      description="Token de autenticação do cliente. Envie no header Authorization: Bearer {token}"
 * )
 */
abstract class Controller
{
    //
}
