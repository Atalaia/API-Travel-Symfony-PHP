<?php

namespace App\Controller;

use App\Entity\Country;
use App\Repository\CountryRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/country")
 */
class CountryController extends AbstractController
{
    /**
     * @Route("/", name="countries", methods={"GET"})
     */
    public function countryList(CountryRepository $countryRepository)
    {
        $countries = $countryRepository->findAll();
        return $this->json($countries);
    }

    /**
     * @Route("/{id}", name="country_detail", methods={"GET"})
     */
    public function countryDetail(CountryRepository $countryRepository, $id):Response
    {
        $country = $countryRepository->find($id);
        return $this->json($country);
    }

    /**
     * @Route("/new", name="country_new", methods={"POST"})
     */
    public function countryNew(Request $request):Response
    {
        $data = json_decode($request->getContent(), true);

        $country = new Country($data['name'], $data['flag']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($country);
        $entityManager->flush();

        return new Response('It worked. Believe me - I\'m an API');  
    }

    /**
     * @Route("/edit/{id}", name="country_edit", methods={"PUT"})
     */
    public function countryEdit($id, CountryRepository $countryRepository, Request $request):Response
    {
        $data= json_decode($request->getContent(), true);

        $country = $countryRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$country) {
            throw $this->createNotFoundException(
                'No country found for id '.$id
            );
        }      
        $country->setName($data['name']);
        $country->setFlag($data['flag']);
        $entityManager->flush();
        return $this->json("Country updated");
    }

    /**
     * @Route("/delete/{id}", name="country_delete", methods={"DELETE"})
     */
    public function delete(CountryRepository $countryRepository, $id)
    {
        $country = $countryRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($country);
        $entityManager->flush();       
        return $this->json("Country deleted");
    }
    

    public function __toString()
    {
        return $this->name;
    }

}
