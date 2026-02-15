import random
import json

def simulate_match():
    # Placar principal
    team_a_goals = random.randint(0, 10)
    team_b_goals = random.randint(0, 10)

    # Critérios de desempate:
    # 1. Fair Play: Cartões amarelos recebidos no jogo
    team_a_cards = random.randint(0, 10)
    team_b_cards = random.randint(0, 10)

    # 2. Pênaltis (será gerado antecipadamente, mas só será usado se necessário)
    team_a_penalties = random.randint(3, 5)
    team_b_penalties = random.randint(3, 5)

    # Garantia de que, se for pros pênaltis, não termine empatado
    while team_a_penalties == team_b_penalties:
        team_b_penalties = random.randint(3, 6)

    result = {
        "team_a": {
            "goals": team_a_goals,
            "yellow_cards": team_a_cards,
            "penalties": team_a_penalties
        },
        "team_b": {
            "goals": team_b_goals,
            "yellow_cards": team_b_cards,
            "penalties": team_b_penalties
        }
    }

    print(json.dumps(result))

if __name__ == "__main__":
    simulate_match()
