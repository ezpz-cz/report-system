#include <sourcemod>
#include <csgocolors>
#include <validClient>
#include <urlencode>
#include <explodeString>

public Plugin:myinfo =  {
	name = "EzPz.cz Report System", 
	author = "gorgitko", 
	description = "Allows to report player and assign an admin to. Needs web frontend/backend.", 
	version = "1.0", 
	url = "ezpz.cz"
};

ConVar g_cvEnableReportSystem;
ConVar g_cvServerId;
elapsedRounds = 0;
new String:trgs_info[64][3][1024];

public OnPluginStart() {
	g_cvEnableReportSystem = CreateConVar("sm_ezpz_report_enabled", "1", "Enables or disables the report system.\n[DEFAULT: 1]");
	g_cvServerId = CreateConVar("sm_ezpz_report_serverid", "0", "Server ID in database.\n[DEFAULT: 0]");
	AutoExecConfig();
	
	LoadTranslations("report-system.phrases");
	
	RegConsoleCmd("sm_report", Menu_Report);
	RegConsoleCmd("sm_nahlasit", Menu_Report);
	RegConsoleCmd("sm_cheater", Menu_Report);
	RegConsoleCmd("sm_cheat", Menu_Report);
	RegConsoleCmd("sm_ab", Menu_Report);
	RegConsoleCmd("sm_aimbot", Menu_Report);
	RegConsoleCmd("sm_wh", Menu_Report);
	RegConsoleCmd("sm_wallhack", Menu_Report);
	RegConsoleCmd("sm_sh", Menu_Report);
	RegConsoleCmd("sm_speedhack", Menu_Report);
	RegConsoleCmd("sm_ohlasit", Menu_Report);
	RegConsoleCmd("sm_hlaseni", Menu_Report);
	
	HookEvent("round_start", Event_RoundStart)
}

public Event_RoundStart(Handle:event, const String:name[], bool:dontBroadcast)
{
	elapsedRounds++;
}

public OnMapEnd()
{
	elapsedRounds = 0;
}

public OnMapStart()
{
	elapsedRounds = 0;
}

public Action:Menu_Report(client, args)
{
	if (GetConVarInt(g_cvEnableReportSystem) == 0)
		return Plugin_Handled;
	
	new Handle:menu = CreateMenu(MenuHandlerswp);
	SetMenuTitle(menu, "Report Player");
	
	new counter = 0;
	decl String:trg_nick[256];
	decl String:trg_sid[64];
	decl String:trg_ip[16];
	decl String:trg_index[4];
	//decl String:trg_info[512];
	
	for (new i = 1; i <= MaxClients; i++)
	{
		if (client == i)
			continue;
		
		//for tests - you can report yourself then
		/*if(IsValidClient(client) == false)
        	continue;*/
		
		if (!IsClientConnected(i) || IsClientInKickQueue(i))
		{
			continue;
		}
		
		if (IsFakeClient(i))
		{
			continue;
		}
		
		if (!IsClientInGame(i))
		{
			continue;
		}
		
		GetClientName(i, trg_nick, sizeof(trg_nick));
		trgs_info[i][0] = trg_nick;
		GetClientAuthId(i, AuthId_Steam2, trg_sid, sizeof(trg_sid));
		trgs_info[i][1] = trg_sid;
		GetClientIP(i, trg_ip, sizeof(trg_ip));
		trgs_info[i][2] = trg_ip;
		Format(trg_index, sizeof(trg_index), "%d", i);
		
		AddMenuItem(menu, trg_index, trg_nick);
		counter++;
	}
	
	if (counter == 0)
	{
		CPrintToChat(client, "{DARKBLUE}[{DARKRED}EzPz.cz{DARKBLUE}] {GREEN}%t", "noplayer");
		return Plugin_Handled;
	}
	
	SetMenuExitButton(menu, true);
	DisplayMenu(menu, client, 20);
	
	return Plugin_Handled;
}

public MenuHandlerswp(Handle:menu, MenuAction:action, param1, param2)
{
	if (action == MenuAction_Select)
	{
		decl String:rep_nick[512];
		decl String:rep_nick_encoded[1024];
		decl String:rep_sid[64];
		decl String:rep_ip[32];
		
		decl String:trg_nick_encoded[1024];
		decl String:trg_index_str[4];
		GetMenuItem(menu, param2, trg_index_str, sizeof(trg_index_str));
		new trg_index = StringToInt(trg_index_str);
		
		decl String:map[64];
		
		if (IsValidClient(param1))
		{
			GetClientName(param1, rep_nick, sizeof(rep_nick));
			GetClientAuthId(param1, AuthId_Steam2, rep_sid, sizeof(rep_sid));
			GetClientIP(param1, rep_ip, sizeof(rep_ip));
		}
		else
		{
			CPrintToChat(param1, "{DARKBLUE}[{DARKRED}EzPz.cz{DARKBLUE}] {RED}%t", "error");
			return 0;
		}
		
		StringURLEncode(rep_nick, rep_nick_encoded, sizeof(rep_nick_encoded));
		ReplaceString(rep_nick_encoded, sizeof(rep_nick_encoded), " ", "%20");
		
		StringURLEncode(trgs_info[trg_index][0], trg_nick_encoded, sizeof(trg_nick_encoded));
		ReplaceString(trg_nick_encoded, sizeof(trg_nick_encoded), " ", "%20");
		
		GetCurrentMap(map, sizeof(map));
		
		decl String:path[PLATFORM_MAX_PATH];
		new String:demo_file[256];
		BuildPath(Path_SM, path, PLATFORM_MAX_PATH, "currentdemo.txt");
		new Handle:fileHandle = OpenFile(path, "r");
		ReadFileLine(fileHandle, demo_file, sizeof(demo_file))
		CloseHandle(fileHandle);
		
		new String:lang[10];
		new languageNumber = GetClientLanguage(param1);
		GetLanguageInfo(languageNumber, lang, sizeof(lang));
		
		new Handle:g_hHostName = FindConVar("hostname");
		new String:server_name[256];
		new String:server_name_encoded[256];
		GetConVarString(g_hHostName, server_name, sizeof(server_name));
		StringURLEncode(server_name, server_name_encoded, sizeof(server_name_encoded));
		ReplaceString(server_name_encoded, sizeof(server_name_encoded), " ", "%20");
		
		new String:result[4096];
		Format(result, sizeof(result), "http://motd-report.ezpz.cz/?rep_nick=%s&rep_sid=%s&rep_ip=%s&trg_nick=%s&trg_sid=%s&trg_ip=%s&server_id=%i&map=%s&round=%i&demo_file=%s&lang=%s&server_name=%s", 
			rep_nick_encoded, rep_sid, rep_ip, trg_nick_encoded, trgs_info[trg_index][1], trgs_info[trg_index][2], GetConVarInt(g_cvServerId), map, elapsedRounds - 1, demo_file, lang, server_name_encoded);
		
		ShowMOTDPanel(param1, "Report", result, MOTDPANEL_TYPE_URL);
	}
	
	if (action == MenuAction_End)
	{
		CloseHandle(menu)
	}
	
	return 0;
} 