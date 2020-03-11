<?php

namespace App\Controller;

use App\Entity\Travel;
use App\Repository\CompanyRepository;
use App\Repository\CountryRepository;
use App\Repository\TravelRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// A Mettre pour serialiser le retour du service en json
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @Route("/travel")
 */
class TravelController extends AbstractController
{
    /**
     * @Route("/", name="travels", methods={"GET"})
     */
    public function travelList(TravelRepository $travelRepository):Response
    {
        $travels = $travelRepository->findAll();
        return new Response($this->serializeJSON($travels));
    }

    /**
     * @Route("/{id}", name="travel_detail", methods={"GET"})
     */
    public function travelDetail(TravelRepository $travelRepository, $id):Response
    {
        $travel = $travelRepository->find($id);
        return new Response($this->serializeJSON($travel));
    }

    /**
     * @Route("/new", name="travel_new", methods={"POST"})
     */
    public function travelNew(Request $request, CompanyRepository $companyRepository, CountryRepository $countryRepository):Response
    {
        $data = json_decode($request->getContent(), true);
        // var_dump($data);

        $date = new \DateTime($data['departure_date']);
        // var_dump($date);

        $countryId = $countryRepository->find($data['country']);
        // var_dump($countryId);
        $companyId = $companyRepository->find($data['company']);

        $travel = new Travel(
            $data['title'], 
            $date, 
            $data['duration'], 
            $data['price'], 
            $countryId, 
            $companyId
        );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($travel);
        $entityManager->flush();

        return new Response('It worked. Believe me - I\'m an API'); 
    }

    /**
     * @Route("/edit/{id}", name="travel_edit", methods={"PUT"})
     */
    public function travelEdit($id, TravelRepository $travelRepository, CompanyRepository $companyRepository, CountryRepository $countryRepository, Request $request):Response
    {
        $data = json_decode($request->getContent(), true);

        $date = new \DateTime($data['departure_date']);

        $countryId = $countryRepository->find($data['country']);
        $companyId = $companyRepository->find($data['company']);

        $travel = $travelRepository->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        if (!$travel) {
            throw $this->createNotFoundException(
                'No travel found for id '.$id
            );
        }      
        $travel->setTitle($data['title']);
        $travel->setDepartureDate($date);
        $travel->setDuration($data['duration']);
        $travel->setPrice($data['price']);
        $travel->setCountry($countryId);
        $travel->setCompany($companyId);

        $entityManager->flush();
        
        return $this->json("Travel updated");
    }

    /**
     * @Route("/delete/{id}", name="travel_delete", methods={"DELETE"})
     */
    public function delete(TravelRepository $travelRepository, $id)
    {
        $travel = $travelRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($travel);
        $entityManager->flush();       
        return $this->json("Travel deleted");
    }


    public function serializeJSON($data) {
        $encoders = [new JsonEncoder()]; // If no need for XmlEncoder
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        //var_dump($serializer->serialize($data, 'json'));

        $dataResponse = $serializer->serialize($data, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return $dataResponse;
    }

}