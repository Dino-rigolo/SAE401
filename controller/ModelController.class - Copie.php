<?php
class Controller {
    private $action;
    private $idArtist;
    private $idGender;
    private $idPlaylist;
    private $idAlbum;
    private $idSalesAgent;
    private $startPlaylistIndex = 0;
    private $endPlaylistIndex = 23;
    private $startTrackIndex = 0;
    private $endTrackIndex = 23;
    private $startTAlbumIndex = 0;
    private $endAlbumIndex = 23;
    private $startIndexGenre = 1;
    private $endIndexGenre = 16;
    private $startIndexGenreRanking = 0;
    private $endIndexGenreRanking = 9;
    private $startIndexCountryRanking = 0;
    private $endIndexCountryRanking = 9;
    private $startIndexArtist = 0;
    private $endIndexArtist = 23;
    private $startAlbumArtist = 0;
    private $endAlbumArtist = 23;
    private $startIndexMusicClient = 0;
    private $endIndexMusicClient = 9;
    private $startIndexBest = 0;
    private $endIndexBest = 10;
    private $previousPage;
    private $playlistName;
    private $newTrack;
    private $FirstName;
    private $LastName;
    private $Company;
    private $Address;
    private $City;
    private $State;
    private $Country;
    private $PostalCode;
    private $Phone;
    private $Fax;
    private $Email;
    private $Title;
    private $SupportRepId;
    private $Password;
    private $BirthDate;
    private $HireDate;
    private $idMusic;
    private $password;
    private $email;
    private $secondAction;

    public function __construct(array $arguments = array()){
        if(!empty($arguments)){
            foreach($arguments as $property => $argument){
                $this->{$property} = $argument;
            }
        }
    }
    
    public function __get($nom){
        return $this->$nom;
    }

    public function __set($nom, $valeur){
        $this->$nom = $valeur;
    }

    public function invoke(){ 
        try {
            // Si aucune action n'est définie
            if(!isset($this->action)){
                if(!isset($_SESSION["compte"])){
                    throw new ControllerException("", "ViewConnection");
                } else {
                    throw new ControllerException("", "ViewCatalogue");
                }
            }

            // Gestion des actions lorsque l'utilisateur n'est pas connecté
            if(!isset($_SESSION["compte"])){
                switch ($this->action) {
                    case 'connection':
                        require_once("model/Employee.class.php");
                        require_once("model/Customer.class.php");                     
                        if(Employee::isEmployee($this->email)){ // Si le mail ressemble à un mail d'employé
                            if (Employee::isEmployeeExisting($this->email, $this->password)) {
                                $_SESSION["compte"] = Employee::isEmployeeExisting($this->email, $this->password);
                                $_SESSION["compte"]->Password = "";
                                throw new ControllerException("", "ViewCatalogue");
                                return;
                            }else { // Si email ou mot de passe incorrect
                                throw new ControllerException("Mot de passe ou email incorrecte", "ViewConnection");
                                return;
                            }
                        } elseif (Customer::search($this->email, $this->password)) { // Si le mail ressemble à un mail de client
                            $_SESSION["compte"] = Customer::search($this->email, $this->password);
                            $_SESSION["compte"]->Password = "";
                            throw new ControllerException("", "ViewCatalogue");
                            return;
                        } else { // Si email ou mot de passe incorrect
                            throw new ControllerException("Mot de passe ou email incorrecte", "ViewConnection");
                            return;
                        }
                        break;
                    case 'ViewConnection':
                        include("view/ViewConnection.php");
                        break;
                    case 'ViewPurchaseHistoryClient':
                        // L'action ViewPurchaseHistoryClient n'est pas accessible sans connexion, redirection vers ViewConnection
                        throw new ControllerException("Action non autorisée sans connexion", "ViewConnection");
                        break;
                    default:
                        throw new ControllerException("Action non reconnue", "ViewConnection");
                        break;
                }
                return;
            }

            // Gestion des actions lorsque l'utilisateur est connecté
            switch ($this->action) {
                case 'ViewCatalogue':
                    require_once("model/Artist.class.php");
                    require_once("model/Track.class.php");
                    require_once("model/Playlist.class.php");
                    require_once("model/Genre.class.php");
                    $Artists = Artist::getSixArtists();
                    $Genres = Genre::getHeightGenres();
                    $Playlists = Playlist::getSixPlaylists();
                    $MostSold = Track::getSixMostSoldTracks();
                    include("view/ViewCatalogue.php");
                    break;
                
                case 'deconnection':
                    unset($_SESSION["compte"]);
                    throw new ControllerException("", "ViewConnection");
                    break;

                case 'ViewMusicClient':
                    require_once("model/Customer.class.php");
                    require_once("model/Track.class.php");
                    require_once("model/Album.class.php");
                    require_once("model/Artist.class.php");
                    $start = (int)$this->startIndexMusicClient;
                    $end = (int)$this->endIndexMusicClient;
                    $trackCustomer = Customer::getAllTrackFromCustomer($_SESSION["compte"]->CustomerId, $start, $end);
                    $limit = sizeof($trackCustomer) < 10;
                    $AlbumsOfTracks = [];
                    $artistsOfTracks = [];
                    
                    foreach ($trackCustomer as $key => $value) {
                        $AlbumsOfTracks[$key] = Album::getAlbumById($value->AlbumId);
                        $artistsOfTracks[$key] = Artist::getArtistById($AlbumsOfTracks[$key]->ArtistId);
                    }
                    $page = "ViewMusicClient";
                    include("view/ViewMusicClient.php");
                    break;

                case 'ViewPurchaseHistoryClient':
                    require_once("model/Invoice.class.php");
                    require_once("model/Employee.class.php");
                    if(Employee::isEmployee($_SESSION["compte"]->Email)) {
                        include("view/IndexView.php");
                    }
                    else {
                        $recentMusics = Invoice::getRecentInvoiceByCustomerId($_SESSION["compte"]->CustomerId);
                        include("view/ViewPurchaseHistoryClient.php");
                    }
                    break;

                case 'ViewBestSellers':
                    require_once("model/Customer.class.php");
                    require_once("model/Track.class.php");
                    require_once("model/Album.class.php");
                    require_once("model/Artist.class.php");
                    $start = (int)$this->startIndexBest;
                    $end = (int)$this->endIndexBest;
                    $bestSellers = Track::getMostSoldTracks($start, $end);
                    $limit = sizeof($bestSellers) < 10;
                    $AlbumsOfTracks = [];
                    $artistsOfTracks = [];
                    foreach ($bestSellers as $key => $value) {
                        $AlbumsOfTracks[$key] = Album::getAlbumById($value->AlbumId);
                        $artistsOfTracks[$key] = Artist::getArtistById($AlbumsOfTracks[$key]->ArtistId);
                    }
                    $page = "ViewBestSellers";
                    include("view/ViewBestSellers.php");
                    break;

                case 'ViewInfo':
                    include("view/ViewInfo.php");
                    break;

                case 'ViewAllPlaylist':
                    require_once("model/Playlist.class.php");
                    $start = (int)$this->startPlaylistIndex;
                    $end = (int)$this->endPlaylistIndex;
                    $Playlists = Playlist::getAllPlaylists($start, $end);
                    $limit = sizeof($Playlists) < 24;
                    $page = "ViewAllPlaylist";
                    include("view/ViewAllPlaylist.php");
                    break;

                case 'ViewAllArtist':
                    require_once("model/Artist.class.php");
                    $start = (int)$this->startIndexArtist;
                    $end = (int)$this->endIndexArtist;
                    $Artists = Artist::getAllArtists($start, $end);
                    $limit = sizeof($Artists) < 24;
                    $page = "ViewAllArtist";
                    include("view/ViewAllArtist.php");
                    break;

                case 'ViewPlaylist':
                    require_once("model/Playlist.class.php");
                    require_once("model/PlaylistTrack.class.php");
                    require_once("model/Track.class.php");
                    require_once("model/Album.class.php");
                    require_once("model/Artist.class.php");

                    $Playlist = Playlist::getPlaylistById($this->idPlaylist);
                    $PlaylistTracks = $Playlist->getAllPlaylistTracks();

                    $tracks = [];
                    $AlbumsOfTracks = [];
                    $artistsOfTracks = [];
                    foreach ($PlaylistTracks as $key => $value) {
                        $track = Track::getTrackById($value->TrackId);
                        $tracks[$key] = $track;
                        $AlbumsOfTracks[$key] = Album::getAlbumById($track->AlbumId);
                        $artistsOfTracks[$key] = Artist::getArtistById($AlbumsOfTracks[$key]->ArtistId);
                    }
                    include("view/ViewPlaylist.php");
                    break;

                case 'ViewAllGenre':
                    require_once("model/Genre.class.php");
                    $start = (int)$this->startIndexGenre;
                    $end = (int)$this->endIndexGenre;
                    $Genres = Genre::getAllGenres($start, $end);
                    $limit = sizeof($Genres) < 16;
                    $page = "ViewAllGenre";
                    include("view/ViewAllGenre.php");
                    break;

                case 'ViewArtist':
                    require_once("model/Artist.class.php");
                    require_once("model/Album.class.php");
                    $start = (int)$this->startAlbumArtist;
                    $end = (int)$this->endAlbumArtist;
                    $Artist = Artist::getArtistById($this->idArtist);
                    $Album = Album::getAlbumByArtist($this->idArtist, $start, $end);
                    include("view/ViewArtist.php");
                    break;

                case 'ViewGender':
                    require_once("model/Genre.class.php");
                    $start = (int)$this->startIndexGenre;
                    $end = (int)$this->endIndexGenre;
                    $Genres = Genre::getAllGenres($start, $end);
                    include("view/ViewGender.php");
                    break;

                case 'ViewAlbum':
                    require_once("model/Album.class.php");
                    require_once("model/Track.class.php");
                    require_once("model/Artist.class.php");
                    $Album = Album::getAlbumById($this->idAlbum);
                    $Artist = Artist::getArtistById($Album->ArtistId);
                    $Tracks = Track::getTracksByAlbum($this->idAlbum);
                    include("view/ViewAlbum.php");
                    break;

                case 'ViewAlbumByArtist':
                    require_once("model/Album.class.php");
                    $Album = Album::getAlbumByArtist($this->idArtist, $this->startTAlbumIndex, $this->endAlbumIndex);
                    $limit = sizeof($Album) < 24;
                    $page = "ViewArtiste";
                    include("view/ViewArtiste.php");
                    break;

                case 'ViewTrack':
                    require_once("model/Track.class.php");
                    require_once("model/Album.class.php");
                    require_once("model/Artist.class.php");
                    require_once("model/MediaType.class.php");
                    $track = Track::getTrackById($this->idMusic);
                    $album = Album::getAlbumById($track->AlbumId);
                    $artist = Artist::getArtistById($album->ArtistId);
                    $mediaType = MediaType::getMediaTypeById($track->MediaTypeId);
                    include("view/ViewTrack.php");
                    break;

                case 'GenderRanking':
                    if ($_SESSION["compte"]->isSalesEmployee()) {
                        require_once("model/Genre.class.php");
                        $start = (int)$this->startIndexGenreRanking;
                        $end = (int)$this->endIndexGenreRanking;
                        $Genres = Genre::getGenderRanking($start, $end);
                        $limit = sizeof($Genres) < 9;
                        $page = "ViewGenderRanking";
                        include("view/ViewGenderRanking.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'ViewGenreTracks':
                    require_once("model/Genre.class.php");
                    require_once("model/Track.class.php");
                    require_once("model/Album.class.php");
                    require_once("model/Artist.class.php");
                    $Genres = Genre::getGenreById($this->idGender);
                    $start = (int)$this->startTrackIndex;
                    $end = (int)$this->endTrackIndex;

                    $tracks = Track::getTracksByGender($this->idGender, $start, $end);
                    $albumsOfTracks = [];
                    $artistsOfTracks = [];
                    foreach ($tracks as $key => $value) {
                        $albumsOfTracks[$key] = Album::getAlbumById($value->AlbumId);
                        $artistsOfTracks[$key] = Artist::getArtistById($albumsOfTracks[$key]->ArtistId);
                    }
                    $limit = sizeof($tracks) < 23;
                    $page = "ViewGenreTracks";
                    include("view/ViewGenreTracks.php");
                    break;

                case 'CountryStats':
                    if ($_SESSION["compte"]->isSalesEmployee()) {
                        require_once("model/Invoice.class.php");
                        $start = (int)$this->startIndexCountryRanking;
                        $end = (int)$this->endIndexCountryRanking;
                        $Stats = Invoice::getCountryRanking($start, $end);
                        $limit = sizeof($Stats) < 9;
                        $page = "ViewCountryStats";
                        include("view/ViewCountryStats.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'AgentResume':
                    if ($_SESSION["compte"]->isSalesEmployee()) {
                        require_once("model/Employee.class.php");
                        $salesAgent = Employee::viewAllSalesAgent();
                        include("view/ViewAllSalesAgent.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'ViewAgentResume':
                    if ($_SESSION["compte"]->isSalesEmployee()) {
                        if(!isset($this->idSalesAgent)){
                            throw new ControllerException("Veuillez sélectionner un agent", "AgentResume");
                        }
                        require_once("model/Employee.class.php");
                        $idAgent = Employee::getEmployeeById($this->idSalesAgent);
                        $countrySales = Employee::getCountrySales($this->idSalesAgent);
                        $genderSales = Employee::getGenderSales($this->idSalesAgent);
                        $clientSales = Employee::getClientSales($this->idSalesAgent);
                        include("view/ViewAgentResume.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'ListCustomer':
                    if ($_SESSION["compte"]->isItStaff()) {
                        require_once("model/Customer.class.php");
                        require_once("model/Employee.class.php");
                        $Customer = Customer::getAllCustomers();
                        $Employee = Employee::getAllEmployees();
                        include("view/ViewListCustomer.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'PlaylistModifier':
                    if ($_SESSION["title"] == "IT") {
                        require_once("model/Playlist.class.php");
                        $start = (int)$this->startPlaylistIndex;
                        $end = (int)$this->endPlaylistIndex;
                        $Playlists = Playlist::getAllPlaylists($start, $end);
                        include("view/ViewPlaylistModifier.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewPlaylist");
                    }
                    break;

                case 'InsertPlaylist':
                    if ($_SESSION["title"] == "IT") {
                        require_once("model/Playlist.class.php");
                        $Playlist = Playlist::createPlaylist($this->playlistName);
                        $this->playlistName = "";
                        // Redirection ou rechargement de la liste des playlists
                        throw new ControllerException("", "ViewPlaylist");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "IndexView");
                    }
                    break;

                case 'DeletePlaylist':
                    if ($_SESSION["title"] == "IT") {
                        require_once("model/Playlist.class.php");
                        $Playlist = Playlist::deletePlaylist($this->idPlaylist);
                        // Redirection ou rechargement de la liste des playlists
                        throw new ControllerException("", "ViewPlaylist");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewPlaylist");
                    }
                    break;

                case 'ModifCustomer':
                    if ($_SESSION["compte"] instanceof Customer) {
                        require_once("model/Customer.class.php");
                        if(!empty($this->Password) || $this->Password==""){
                            $this->Password = $_SESSION["compte"]->Password;
                        }

                        Customer::ModifCustomer(
                            $_SESSION["compte"]->CustomerId, 
                            $this->FirstName, 
                            $this->LastName, 
                            $this->Company, 
                            $this->Address, 
                            $this->City, 
                            $this->State, 
                            $this->Country, 
                            $this->PostalCode, 
                            $this->Phone, 
                            $this->Fax, 
                            $this->Email, 
                            $this->Password
                        );
                        $_SESSION["compte"] = Customer::getCustomerById($_SESSION["compte"]->CustomerId);
                        $_SESSION["compte"]->Password = "";
                        $message = "Vos informations ont bien été modifiées";
                        include("view/ViewInfo.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'ModifEmployee':
                    if ($_SESSION["compte"] instanceof Employee) {
                        require_once("model/Employee.class.php");
                        if(!empty($this->Password) || $this->Password==""){
                            $this->Password = $_SESSION["compte"]->Password;
                        }
                        echo $this->Password;
                        Employee::ModifEmployee(
                            $_SESSION["compte"]->EmployeeId, 
                            $this->FirstName, 
                            $this->LastName, 
                            $this->Address, 
                            $this->City, 
                            $this->State, 
                            $this->Country, 
                            $this->PostalCode, 
                            $this->Phone, 
                            $this->Fax, 
                            $this->Password
                        );
                        $_SESSION["compte"] = Employee::getEmployeeById($_SESSION["compte"]->EmployeeId);
                        $_SESSION["compte"]->Password = "";
                        $message = "Vos informations ont bien été modifiées";
                        include("view/ViewInfo.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'ModifAllPlaylist':
                    if ($_SESSION["compte"]->isItStaff()) {
                        require_once("model/Playlist.class.php");

                        if (!empty($this->playlistName) && $this->secondAction == "addPlaylist") {
                            Playlist::createPlaylist($this->playlistName);
                            $this->playlistName = "";
                        }
                        if (!empty($this->idPlaylist) && $this->secondAction == "removePlaylist") {
                            Playlist::deletePlaylist($this->idPlaylist);
                        }

                        $start = (int)$this->startPlaylistIndex;
                        $end = (int)$this->endPlaylistIndex;
                        $Playlists = Playlist::getAllPlaylists($start, $end);
                        $limit = sizeof($Playlists) < 24;
                        $page = "ViewPlaylistCustomer";
                        include("view/ViewPlaylistCustomer.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                case 'modifPlaylist':
                    if ($_SESSION["compte"]->isItStaff()){
                        require_once("model/Playlist.class.php");
                        require_once("model/PlaylistTrack.class.php");
                        require_once("model/Track.class.php");
                        require_once("model/Album.class.php");
                        require_once("model/Artist.class.php");
                        if(!isset($this->idPlaylist)){
                            throw new ControllerException("Veuillez sélectionner une playlist", "ViewPlaylist");
                        }


                        $Playlist = Playlist::getPlaylistById($this->idPlaylist);
                        $allTracks = Track::getAllTracks();
                        $PlaylistTracks = $Playlist->getAllPlaylistTracks();

                        if (!empty($this->newTrack) && $this->secondAction == "addTrack") {
                            PlaylistTrack::addPlaylistTrack($this->idPlaylist, $this->newTrack);
                            $this->newTrack = "";
                            // Redirection pour recharger la page avec les modifications
                            throw new ControllerException("", "modifPlaylist&idPlaylist={$this->idPlaylist}");
                            exit;
                        }

                        if (!empty($this->newTrack) && $this->secondAction == "removeTrack") {
                            PlaylistTrack::removePlaylistTrack($this->idPlaylist, $this->newTrack);
                            $this->newTrack = "";
                            // Redirection pour recharger la page avec les modifications
                            throw new ControllerException("", "modifPlaylist&idPlaylist={$this->idPlaylist}");
                            exit;
                        }

                        $tracks = [];
                        $AlbumsOfTracks = [];
                        $artistsOfTracks = [];
                        foreach ($PlaylistTracks as $key => $value) {
                            $track = Track::getTrackById($value->TrackId);
                            $tracks[$key] = $track;
                            $AlbumsOfTracks[$key] = Album::getAlbumById($track->AlbumId);
                            $artistsOfTracks[$key] = Artist::getArtistById($AlbumsOfTracks[$key]->ArtistId);
                        }
                        include("view/ViewModifPlaylist.php");
                    }
                    else {
                        throw new ControllerException("Accès non autorisé", "ViewCatalogue");
                    }
                    break;

                default:
                    throw new ControllerException("Action non reconnue", "ViewCatalogue");
                    break;
            }
            return;

        } catch (ControllerException $e) {
            $e->redirect();
        }

    }
}
?>