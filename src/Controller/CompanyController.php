<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company")
 */
class CompanyController extends AbstractController
{
    /**
     * @Route("/", name="companies", methods={"GET"})
     */
    public function companyList(CompanyRepository $companyRepository)
    {
        $companies = $companyRepository->findAll();
        return $this->json($companies);
    }

    /**
     * @Route("/{id}", name="company_detail", methods={"GET"})
     */
    public function companyDetail(CompanyRepository $companyRepository, $id):Response
    {
        $company = $companyRepository->find($id);
        return $this->json($company);
    }

    /**
     * @Route("/new", name="company_new", methods={"POST"})
     */
    public function companyNew(Request $request):Response
    {
        $data = json_decode($request->getContent(), true);

        $company = new Company($data['name'], $data['address'], $data['zip_code'], $data['city'], $data['photo']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($company);
        $entityManager->flush();

        return new Response('It worked. Believe me - I\'m an API');  
    }

    /**
     * @Route("/edit/{id}", name="company_edit", methods={"PUT"})
     */
    public function companyEdit($id, CompanyRepository $companyRepository, Request $request):Response
    {
        $data= json_decode($request->getContent(), true);

        $company = $companyRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$company) {
            throw $this->createNotFoundException(
                'No company found for id '.$id
            );
        }      

        $company->setName($data['name']);
        $company->setAddress($data['address']);
        $company->setZipCode($data['zip_code']);
        $company->setCity($data['city']);
        $company->setPhoto($data['photo']);
        $entityManager->flush();

        return $this->json("Company updated");
    }

    /**
     * @Route("/delete/{id}", name="company_delete", methods={"DELETE"})
     */
    public function delete(CompanyRepository $companyRepository, $id)
    {
        $company = $companyRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($company);
        $entityManager->flush();       
        return $this->json("Company deleted");
    }
    

    public function __toString()
    {
        return $this->name;
    }

}
