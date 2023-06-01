<?php

namespace App\Http\Controllers;

use App\Models\MyUser;
use App\Models\TypePannes;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\session;
use App\Http\Controllers\MyUserController;

class TicketController extends Controller
{
    /**
     * liste l'ensemble des tickets
     * 
     * @return redirect vers la vue de tous les tickets
     */
    public function getTickets()
    {
        if (!empty(session()->get('idUser') & !empty(session()->get('IsTecHotline')))) {
        // if (Auth::id()) { // TODO

            // $IsTecHotline = self::isTecHoline();
            // $IsTecHotline = $this->UserisTecHoline();
            // dd(session()->all());
            $db = new Ticket();
            $data = $db->getTickets();
            return view('tickets', ['data' => $data, 'IsTecHotline' => session()->get('IsTecHotline')]);
        }
    }

    /**
     * Liste les tickets pour un utilisatuer suivant son identifiant en session
     * 
     * @return redirect vers la vue de tous les tickets de l'utilisateur
     */
    public function getMyTickets()
    {
        
        // MyUserController::getUserIdToSession($request->user()->id);

        $db = new Ticket();
        $data = $db->getMyTickets(session()->get('idUser'));
        // dd($data);

        return view('tickets', ['data' => $data]);
    }

    /**
     * Revoie vers la vue de création d'un nouveau ticket, incident
     * 
     * @return view
     */
    public function getNewTicket()
    {
        $dbPannes = new TypePannes();
        $ListePannes = $dbPannes->getAllFailures();

        // dd(session()->all());
        return view('newticket', ['liste_pannes' => $ListePannes]);
    }

    /**
     * Ajout un nouveau ticket, incident
     * 
     * @param Request requête provenant du formulaire en Post
     * @return redirect vers la vue du des messages du nouveau ticket
     */
    public function postNewTicket(Request $request)
    {
        $this->validate($request, [
            'message' => 'required|min:2',
            'sujet' => 'required|min:2'
        ]);

        $newIdTicket = self::getNewID();
        $newIdMessage = MessageController::getNewID();
        $idUser = session()->get('idUser');
        $Sujet = strval($request->input('sujet'));
        $PanneType = (int)$request->input('panne_type');
        $Message = strval($request->input('message'));

        if ($Sujet != Null | $PanneType != Null | $Message != Null) {
            $dbNewTicket = new Ticket();
            $NewTicket = $dbNewTicket->postMyTicket($newIdTicket, $newIdMessage, $Sujet, $PanneType, $idUser, $Message);
            if ($NewTicket) {
                return redirect()->route('ticket', ['nb' => $newIdTicket]);
            } else {
                session()->flash('error', "Votre nouvel incident n'est pas enregistré suite à une erreur de la base de données.\nVeuillez recommencer");
                return redirect()->route('newticket');
            }
        } else {
            session()->flash('error', "Votre nouvel incident n'est pas enregistré, il existe une erreur dans vos données envoyées à la base de données.\nVeuillez recommencer");
            return redirect()->route('newticket');
        }
    }

    /**
     * Cloture un ticket donné
     * 
     * @param int $IdTicket Identifiant du ticket
     * @return redirect route tickets (même page) 
     */
    public function updateToCloseThisTicket(int $IdTicket){

        $dbTicket = new Ticket();
        $data  = $dbTicket->updateToCloseThisTicket($IdTicket);
        if ($data) {
            session()->flash('success', "L'incident est clôturé");
        }else{
            session()->flash('error', "L'incident n'est pas clôturé");
        }
        return redirect()->route('ticket', ['nb' => $IdTicket]);
    }


    // Définition du nouvel Id pour le message
    private static function getNewID()
    {
        $IdMax = TICKET::getMaxId();
        return $IdMax->max + 1;
    }


    
}
